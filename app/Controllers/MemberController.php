<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\BookingModel;
use App\Models\ClassWaitlistModel;
use App\Models\GymClassModel;
use App\Models\MembershipModel;
use App\Models\MembershipPlanModel;
use App\Models\PaymentModel;
use App\Models\UserModel;
use App\Services\BookingService;

class MemberController extends Controller
{
    public function dashboard(): void
    {
        $this->requireMember();

        $user = current_user();
        $membershipModel = new MembershipModel();
        $bookingModel = new BookingModel();
        $classModel = new GymClassModel();
        $paymentModel = new PaymentModel();
        $membership = $membershipModel->currentForUser((int) $user['id']);

        $paymentState = (string) ($_GET['payment'] ?? '');
        if ($paymentState === 'success') {
            flash('success', 'Payment received. Final confirmation may take a few seconds.');
        } elseif ($paymentState === 'cancelled') {
            flash('error', 'Checkout was cancelled. You can resume payment anytime.');
        }

        $expiringSoon = false;
        if ($membership && ($membership['status'] ?? '') === 'active') {
            $end = strtotime((string) $membership['end_date']);
            if ($end !== false) {
                $days = (int) floor(($end - strtotime('today')) / 86400);
                $expiringSoon = $days >= 0 && $days <= 7;
            }
        }

        $this->render('pages/member_dashboard', [
            'title' => 'Member Dashboard',
            'membership' => $membership,
            'history' => $membershipModel->historyForUser((int) $user['id']),
            'bookings' => $bookingModel->userBookings((int) $user['id']),
            'classes' => $classModel->upcomingActive(),
            'billingHistory' => $paymentModel->billingHistoryForUser((int) $user['id']),
            'pendingPayment' => $paymentModel->findLatestPendingForUser((int) $user['id']),
            'failedPayment' => $paymentModel->findRecentFailedForUser((int) $user['id']),
            'expiringSoon' => $expiringSoon,
        ]);
    }

    public function profile(): void
    {
        $this->requireMember();
        $this->render('pages/profile', ['title' => 'Profile', 'user' => current_user()]);
    }

    public function updateProfile(): void
    {
        $this->requireMember();
        verify_csrf();

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if (!Validator::required($fullName)) {
            flash('error', 'Full name is required.');
            redirect('/member/profile');
        }

        $user = current_user();
        $userModel = new UserModel();
        $userModel->updateBasic((int) $user['id'], $fullName, $phone);

        flash('success', 'Profile updated successfully.');
        redirect('/member/profile');
    }

    public function subscribe(): void
    {
        $this->requireMember();
        verify_csrf();

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $planModel = new MembershipPlanModel();
        $plan = $planModel->find($planId);

        if (!$plan || $plan['status'] !== 'active') {
            flash('error', 'Selected plan is unavailable.');
            redirect('/plans');
        }

        $start = new \DateTime('today');
        $end = (clone $start)->modify('+' . (int) $plan['duration_months'] . ' months');

        $membershipModel = new MembershipModel();
        $membershipModel->create((int) current_user()['id'], $planId, $start->format('Y-m-d'), $end->format('Y-m-d'), 'active');

        flash('success', 'Membership subscribed successfully.');
        redirect('/member/dashboard');
    }

    public function renew(): void
    {
        $this->requireMember();
        verify_csrf();

        $planId = (int) ($_POST['plan_id'] ?? 0);
        $planModel = new MembershipPlanModel();
        $plan = $planModel->find($planId);

        if (!$plan || $plan['status'] !== 'active') {
            flash('error', 'Selected plan is unavailable.');
            redirect('/member/dashboard');
        }

        $membershipModel = new MembershipModel();
        $current = $membershipModel->currentForUser((int) current_user()['id']);

        $baseDate = new \DateTime('today');
        if ($current && in_array($current['status'], ['active', 'expired'], true) && strtotime($current['end_date']) > time()) {
            $baseDate = new \DateTime($current['end_date']);
        }
        $endDate = (clone $baseDate)->modify('+' . (int) $plan['duration_months'] . ' months');

        $membershipModel->create((int) current_user()['id'], $planId, $baseDate->format('Y-m-d'), $endDate->format('Y-m-d'), 'active');

        flash('success', 'Membership renewed.');
        redirect('/member/dashboard');
    }

    public function cancelMembership(): void
    {
        $this->requireMember();
        verify_csrf();

        $membershipId = (int) ($_POST['membership_id'] ?? 0);
        $membershipModel = new MembershipModel();
        $membershipModel->cancel($membershipId, (int) current_user()['id']);

        flash('success', 'Membership cancelled.');
        redirect('/member/dashboard');
    }

    public function bookings(): void
    {
        $this->requireMember();

        $bookingModel = new BookingModel();
        $waitlistModel = new ClassWaitlistModel();
        $classModel = new GymClassModel();
        $userId = (int) current_user()['id'];

        $this->render('pages/bookings', [
            'title' => 'My Bookings',
            'bookings' => $bookingModel->userBookings($userId),
            'waitlistEntries' => $waitlistModel->userWaitingEntries($userId),
            'classes' => $classModel->upcomingActive(),
        ]);
    }

    public function bookClass(): void
    {
        $this->requireMember();
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        if ($classId < 1) {
            flash('error', 'Invalid class selection.');
            redirect('/member/bookings');
        }

        $status = (new BookingService())->bookOrWaitlist((int) current_user()['id'], $classId);

        if ($status === 'booked') {
            flash('success', 'Class booked successfully.');
        } elseif ($status === 'waitlisted') {
            flash('success', 'Class is full. You have been added to the waitlist.');
        } elseif ($status === 'already_waitlisted') {
            flash('error', 'You are already on the waitlist for this class.');
        } elseif ($status === 'already_booked') {
            flash('error', 'You already booked this class.');
        } else {
            flash('error', 'Class not available.');
        }

        redirect('/member/bookings');
    }

    public function cancelBooking(): void
    {
        $this->requireMember();
        verify_csrf();

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        if ($bookingId < 1) {
            flash('error', 'Invalid booking selection.');
            redirect('/member/bookings');
        }

        $status = (new BookingService())->cancelBookingAndPromote((int) current_user()['id'], $bookingId);
        if ($status === 'cancelled_promoted') {
            flash('success', 'Booking cancelled. A waitlisted member was auto-promoted.');
        } elseif ($status === 'cancelled') {
            flash('success', 'Booking cancelled.');
        } else {
            flash('error', 'Unable to cancel this booking.');
        }

        redirect('/member/bookings');
    }

    public function cancelWaitlist(): void
    {
        $this->requireMember();
        verify_csrf();

        $waitlistId = (int) ($_POST['waitlist_id'] ?? 0);
        if ($waitlistId < 1) {
            flash('error', 'Invalid waitlist entry.');
            redirect('/member/bookings');
        }

        $cancelled = (new ClassWaitlistModel())->cancelByMember($waitlistId, (int) current_user()['id']);
        flash($cancelled ? 'success' : 'error', $cancelled ? 'Removed from waitlist.' : 'Unable to remove waitlist entry.');
        redirect('/member/bookings');
    }

    public function downloadInvoice(): void
    {
        $this->requireMember();

        $invoiceId = (int) ($_GET['invoice_id'] ?? 0);
        if ($invoiceId < 1) {
            flash('error', 'Invalid invoice selection.');
            redirect('/member/dashboard#billing');
        }

        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->findOwnedById($invoiceId, (int) current_user()['id']);
        if (!$invoice) {
            flash('error', 'Invoice not found.');
            redirect('/member/dashboard#billing');
        }

        $relativePath = ltrim((string) ($invoice['pdf_path'] ?? ''), '/');
        $baseDir = realpath(dirname(__DIR__, 2) . '/public');
        $absolutePath = realpath(dirname(__DIR__, 2) . '/public/' . $relativePath);
        if ($baseDir === false || $absolutePath === false || strpos($absolutePath, $baseDir) !== 0 || !is_file($absolutePath)) {
            flash('error', 'Invoice file is unavailable.');
            redirect('/member/dashboard#billing');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($absolutePath) . '"');
        header('Content-Length: ' . (string) filesize($absolutePath));
        readfile($absolutePath);
        exit;
    }
}
