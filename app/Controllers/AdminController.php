<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\BookingModel;
use App\Models\ContactMessageModel;
use App\Models\GymClassModel;
use App\Models\LocationModel;
use App\Models\MembershipModel;
use App\Models\MembershipPlanModel;
use App\Models\TrainerModel;
use App\Models\UserModel;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel = new UserModel();
        $membershipModel = new MembershipModel();
        $bookingModel = new BookingModel();

        $popular = $bookingModel->popularClass();

        $this->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'totalUsers' => count($userModel->allWithRole()),
            'activeMemberships' => $membershipModel->countActive(),
            'totalBookings' => $bookingModel->countAll(),
            'popularClass' => $popular['title'] ?? 'N/A',
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();
        $userModel = new UserModel();
        $this->render('admin/users', ['title' => 'Manage Users', 'users' => $userModel->allWithRole()]);
    }

    public function createUser(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? 2);

        if (!Validator::required($fullName) || !Validator::email($email) || !Validator::min($password, 8)) {
            flash('error', 'Invalid user details.');
            redirect('/admin/users');
        }

        $userModel = new UserModel();
        if ($userModel->findByEmail($email)) {
            flash('error', 'Email already exists.');
            redirect('/admin/users');
        }

        $userModel->create([
            'role_id' => in_array($roleId, [1, 2], true) ? $roleId : 2,
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone,
        ]);

        flash('success', 'User created.');
        redirect('/admin/users');
    }

    public function updateUser(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 2);
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($id < 1 || !Validator::required($fullName) || !Validator::email($email)) {
            flash('error', 'Invalid user update data.');
            redirect('/admin/users');
        }

        $userModel = new UserModel();
        $userModel->updateByAdmin($id, in_array($roleId, [1, 2], true) ? $roleId : 2, $fullName, $email, $phone);

        flash('success', 'User updated.');
        redirect('/admin/users');
    }

    public function deleteUser(): void
    {
        $this->requireAdmin();
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) current_user()['id']) {
            flash('error', 'You cannot delete your own account.');
            redirect('/admin/users');
        }

        (new UserModel())->delete($id);
        flash('success', 'User deleted.');
        redirect('/admin/users');
    }

    public function plans(): void
    {
        $this->requireAdmin();
        $this->render('admin/plans', ['title' => 'Manage Membership Plans', 'plans' => (new MembershipPlanModel())->all()]);
    }

    public function createPlan(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new MembershipPlanModel())->create($this->planPayload());
        flash('success', 'Plan created.'); redirect('/admin/plans');
    }

    public function updatePlan(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new MembershipPlanModel())->update((int) $_POST['id'], $this->planPayload());
        flash('success', 'Plan updated.'); redirect('/admin/plans');
    }

    public function deletePlan(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new MembershipPlanModel())->delete((int) $_POST['id']);
        flash('success', 'Plan deleted.'); redirect('/admin/plans');
    }

    private function planPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'price' => (float) $_POST['price'],
            'duration_months' => (int) $_POST['duration_months'],
            'description' => trim((string) $_POST['description']),
            'status' => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
        ];
    }

    public function trainers(): void
    {
        $this->requireAdmin();
        $this->render('admin/trainers', ['title' => 'Manage Trainers', 'trainers' => (new TrainerModel())->all()]);
    }

    public function createTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new TrainerModel())->create($this->trainerPayload());
        flash('success', 'Trainer created.'); redirect('/admin/trainers');
    }

    public function updateTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new TrainerModel())->update((int) $_POST['id'], $this->trainerPayload());
        flash('success', 'Trainer updated.'); redirect('/admin/trainers');
    }

    public function deleteTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new TrainerModel())->delete((int) $_POST['id']);
        flash('success', 'Trainer deleted.'); redirect('/admin/trainers');
    }

    private function trainerPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'specialty' => trim((string) $_POST['specialty']),
            'bio' => trim((string) $_POST['bio']),
            'image_path' => trim((string) ($_POST['image_path'] ?? '')),
            'status' => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
        ];
    }

    public function classes(): void
    {
        $this->requireAdmin();
        $this->render('admin/classes', [
            'title' => 'Manage Classes',
            'classes' => (new GymClassModel())->all(),
            'trainers' => (new TrainerModel())->all(),
            'locations' => (new LocationModel())->all(),
        ]);
    }

    public function createClass(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new GymClassModel())->create($this->classPayload());
        flash('success', 'Class created.'); redirect('/admin/classes');
    }

    public function updateClass(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new GymClassModel())->update((int) $_POST['id'], $this->classPayload());
        flash('success', 'Class updated.'); redirect('/admin/classes');
    }

    public function deleteClass(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new GymClassModel())->delete((int) $_POST['id']);
        flash('success', 'Class deleted.'); redirect('/admin/classes');
    }

    private function classPayload(): array
    {
        return [
            'trainer_id' => (int) $_POST['trainer_id'],
            'location_id' => (int) $_POST['location_id'],
            'title' => trim((string) $_POST['title']),
            'description' => trim((string) $_POST['description']),
            'class_date' => trim((string) $_POST['class_date']),
            'start_time' => trim((string) $_POST['start_time']),
            'end_time' => trim((string) $_POST['end_time']),
            'capacity' => (int) $_POST['capacity'],
            'status' => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
        ];
    }

    public function locations(): void
    {
        $this->requireAdmin();
        $this->render('admin/locations', ['title' => 'Manage Locations', 'locations' => (new LocationModel())->all()]);
    }

    public function createLocation(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new LocationModel())->create($this->locationPayload());
        flash('success', 'Location created.'); redirect('/admin/locations');
    }

    public function updateLocation(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new LocationModel())->update((int) $_POST['id'], $this->locationPayload());
        flash('success', 'Location updated.'); redirect('/admin/locations');
    }

    public function deleteLocation(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new LocationModel())->delete((int) $_POST['id']);
        flash('success', 'Location deleted.'); redirect('/admin/locations');
    }

    private function locationPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'address' => trim((string) $_POST['address']),
            'phone' => trim((string) $_POST['phone']),
            'opening_hours' => trim((string) $_POST['opening_hours']),
            'status' => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
            'latitude'      => trim($_POST['latitude'] ?? ''),
            'longitude'     => trim($_POST['longitude'] ?? ''),
            'map_place_id'  => trim($_POST['map_place_id'] ?? ''),
            'image_path'    => trim($_POST['image_path'] ?? '')
        ];
    }

    public function bookings(): void
    {
        $this->requireAdmin();
        $this->render('admin/bookings', ['title' => 'Manage Bookings', 'bookings' => (new BookingModel())->allWithDetails()]);
    }

    public function updateBooking(): void
    {
        $this->requireAdmin(); verify_csrf();
        $status = ($_POST['booking_status'] ?? 'booked');
        $status = in_array($status, ['booked', 'cancelled', 'completed'], true) ? $status : 'booked';
        (new BookingModel())->updateStatus((int) $_POST['id'], $status);
        flash('success', 'Booking updated.'); redirect('/admin/bookings');
    }

    public function deleteBooking(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new BookingModel())->delete((int) $_POST['id']);
        flash('success', 'Booking deleted.'); redirect('/admin/bookings');
    }

    public function messages(): void
    {
        $this->requireAdmin();
        $this->render('admin/messages', ['title' => 'Contact Messages', 'messages' => (new ContactMessageModel())->all()]);
    }

    public function deleteMessage(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new ContactMessageModel())->delete((int) $_POST['id']);
        flash('success', 'Message deleted.'); redirect('/admin/messages');
    }
}

