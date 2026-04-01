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
        $waitlistModel = new ClassWaitlistModel();
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
            'waitlistEntries' => $waitlistModel->userWaitingEntries((int) $user['id']),
            'billingHistory' => $paymentModel->billingHistoryForUser((int) $user['id']),
            'pendingPayment' => $paymentModel->findLatestPendingForUser((int) $user['id']),
            'failedPayment' => $paymentModel->findRecentFailedForUser((int) $user['id']),
            'expiringSoon' => $expiringSoon,
        ]);
    }

    public function profile(): void
    {
        $this->requireMember();

        $user = current_user();
        $membership = (new MembershipModel())->currentForUser((int) $user['id']);

        $this->render('pages/profile', [
            'title' => 'Profile',
            'user' => $user,
            'membership' => $membership,
        ]);
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
        $profileImagePath = (string) ($user['profile_image_path'] ?? '');

        if (isset($_FILES['profile_image']) && is_array($_FILES['profile_image'])) {
            $errorCode = (int) ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $profileImagePath = $this->storeProfileImage($_FILES['profile_image'], (int) $user['id'], $profileImagePath);
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/member/profile');
                }
            }
        }

        $userModel = new UserModel();
        $userModel->updateBasicWithImage(
            (int) $user['id'],
            $fullName,
            $phone,
            $profileImagePath !== '' ? $profileImagePath : null
        );

        flash('success', 'Profile updated successfully.');
        redirect('/member/profile');
    }

    public function deleteProfile(): void
    {
        $this->requireMember();
        verify_csrf();

        $user = current_user();
        $userId = (int) $user['id'];
        $this->removeProfileImage((string) ($user['profile_image_path'] ?? ''));

        (new UserModel())->delete($userId);

        unset($_SESSION['user_id']);
        flash('success', 'Your account was deleted successfully.');
        redirect('/');
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
        $redirectTo = $this->resolveRedirectTarget('/member/bookings');

        $classId = (int) ($_POST['class_id'] ?? 0);
        if ($classId < 1) {
            flash('error', 'Invalid class selection.');
            redirect($redirectTo);
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

        redirect($redirectTo);
    }

    public function cancelBooking(): void
    {
        $this->requireMember();
        verify_csrf();
        $redirectTo = $this->resolveRedirectTarget('/member/bookings');

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        if ($bookingId < 1) {
            flash('error', 'Invalid booking selection.');
            redirect($redirectTo);
        }

        $status = (new BookingService())->cancelBookingAndPromote((int) current_user()['id'], $bookingId);
        if ($status === 'cancelled_promoted') {
            flash('success', 'Booking cancelled. A waitlisted member was auto-promoted.');
        } elseif ($status === 'cancelled') {
            flash('success', 'Booking cancelled.');
        } else {
            flash('error', 'Unable to cancel this booking.');
        }

        redirect($redirectTo);
    }

    public function cancelWaitlist(): void
    {
        $this->requireMember();
        verify_csrf();
        $redirectTo = $this->resolveRedirectTarget('/member/bookings');

        $waitlistId = (int) ($_POST['waitlist_id'] ?? 0);
        if ($waitlistId < 1) {
            flash('error', 'Invalid waitlist entry.');
            redirect($redirectTo);
        }

        $cancelled = (new ClassWaitlistModel())->cancelByMember($waitlistId, (int) current_user()['id']);
        flash($cancelled ? 'success' : 'error', $cancelled ? 'Removed from waitlist.' : 'Unable to remove waitlist entry.');
        redirect($redirectTo);
    }

    private function resolveRedirectTarget(string $defaultPath): string
    {
        $target = trim((string) ($_POST['redirect_to'] ?? ''));
        if ($target === '') {
            return $defaultPath;
        }

        $allowedPaths = [
            '/member/bookings',
            '/member/dashboard',
            '/schedule',
        ];

        $path = parse_url($target, PHP_URL_PATH);
        if (!is_string($path) || !in_array($path, $allowedPaths, true)) {
            return $defaultPath;
        }

        return $target;
    }

    private function storeProfileImage(array $file, int $userId, string $existingImagePath): string
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Unable to upload image. Please try a different file.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > (3 * 1024 * 1024)) {
            throw new \RuntimeException('Profile image must be under 3MB.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Invalid upload source.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($tmpName);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowed[$mimeType])) {
            throw new \RuntimeException('Only JPG, PNG, WEBP, or GIF images are allowed.');
        }

        $relativeDirectory = '/assets/images/profiles';
        $absoluteDirectory = dirname(__DIR__, 2) . '/public' . $relativeDirectory;
        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new \RuntimeException('Unable to prepare profile image directory.');
        }

        $fileName = 'profile-' . $userId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mimeType];
        $absolutePath = $absoluteDirectory . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new \RuntimeException('Failed to save uploaded image.');
        }

        $this->removeProfileImage($existingImagePath);

        return $relativeDirectory . '/' . $fileName;
    }

    private function removeProfileImage(string $relativePath): void
    {
        if ($relativePath === '' || !str_starts_with($relativePath, '/assets/images/profiles/')) {
            return;
        }

        $absolutePath = dirname(__DIR__, 2) . '/public' . $relativePath;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}
