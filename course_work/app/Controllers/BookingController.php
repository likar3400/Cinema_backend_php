<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Http\Response;
use App\Models\{BookingModel, SessionModel};

class BookingController extends Controller
{
    private BookingModel $bookings;
    private SessionModel $sessions;

    public function __construct($r)
    {
        parent::__construct($r);
        $this->bookings = new BookingModel();
        $this->sessions = new SessionModel();
    }

    public function seatMap(array $p): void
    {
        $this->requireAuth();
        $sessionId = (int)($p['id'] ?? 0);
        $session   = $this->sessions->find($sessionId);

        if (!$session || !$session['is_active']) {
            Response::status(404);
            $this->view('error/404', ['title' => '404', 'message' => 'Сеанс не знайдено']);
            return;
        }

        Response::noCache();
        $this->view('booking/seats', [
            'title'   => 'Вибір місця — ' . APP_NAME,
            'session' => $session,
            'seats'   => $this->sessions->getSeats((int)$session['hall_id']),
            'booked'  => $this->sessions->getBookedSeatIds($sessionId),
        ]);
    }

    public function book(array $p): void
    {
        $this->requireAuth();
        Response::noCache();

        $data      = $this->request->json();
        $sessionId = (int)($data['session_id'] ?? 0);

        $csrfKey = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : '_csrf';
        $token   = $data[$csrfKey] ?? $data['_csrf'] ?? '';
        if (!hash_equals(Session::csrfToken(), $token)) {
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);
        }

        $session = $this->sessions->find($sessionId);
        if (!$session) {
            $this->json(['success' => false, 'message' => 'Сеанс не знайдено'], 404);
        }

        if (!empty($data['seats']) && is_array($data['seats'])) {
            $seatIds = array_map('intval', $data['seats']);
        } else {
            $seatId  = (int)($data['seat_id'] ?? 0);
            $seatIds = $seatId ? [$seatId] : [];
        }

        if (empty($seatIds)) {
            $this->json(['success' => false, 'message' => 'Оберіть місце'], 422);
        }

        $allSeats = $this->sessions->getSeats((int)$session['hall_id']);
        $seatsMap = array_column($allSeats, null, 'id');

        $results     = [];
        $ticketCodes = [];
        $totalPrice  = 0;

        try {
            foreach ($seatIds as $seatId) {
                if (!isset($seatsMap[$seatId])) {
                    $this->json(['success' => false, 'message' => "Місце #{$seatId} не знайдено"], 404);
                }

                $seatInfo = $seatsMap[$seatId];
                $price = ($seatInfo['type'] === 'vip' && $session['price_vip'] > 0)
                    ? (float)$session['price_vip']
                    : (float)$session['price'];

                $result = $this->bookings->book(Session::userId(), $sessionId, $seatId, $price);

                if (!$result['success']) {
                    $this->json(['success' => false, 'message' => $result['message']], 409);
                }

                $ticketCodes[] = $result['ticket_code'] ?? ('TC-' . $seatId);
                $totalPrice   += $result['price'] ?? $price;
                $results[]     = $result;
            }
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Помилка сервера: ' . $e->getMessage()], 500);
        }

        $this->json([
            'success'     => true,
            'ticket_code' => implode(', ', $ticketCodes),
            'price'       => $totalPrice,
            'count'       => count($results),
        ], 201);
    }

    public function myBookings(array $p): void
    {
        $this->requireAuth();
        Response::noCache();
        $this->view('booking/my', [
            'title'    => 'Мої квитки — ' . APP_NAME,
            'bookings' => $this->bookings->getByUser(Session::userId()),
        ]);
    }

    public function cancel(array $p): void
    {
        $this->requireAuth();
        Response::noCache();

        $data = $this->request->json();
        $id   = (int)($data['booking_id'] ?? 0);

        $csrfKey = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : '_csrf';
        $token   = $data[$csrfKey] ?? $data['_csrf'] ?? '';

        if (!hash_equals(Session::csrfToken(), $token)) {
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);
        }

        $booking = $this->bookings->find($id);
        if (!$booking || (int)$booking['user_id'] !== Session::userId()) {
            $this->json(['success' => false, 'message' => 'Не знайдено'], 404);
        }

        $this->json(['success' => $this->bookings->cancel($id, Session::userId())]);
    }
}