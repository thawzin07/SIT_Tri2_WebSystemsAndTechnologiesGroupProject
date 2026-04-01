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
        $payload = $this->trainerPayload();
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $payload['image_path'] = $this->storeTrainerImage($_FILES['image']);
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/admin/trainers');
                }
            }
        }
        (new TrainerModel())->create($payload);
        flash('success', 'Trainer created.'); redirect('/admin/trainers');
    }

    public function updateTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        $id = (int) $_POST['id'];
        $trainerModel = new TrainerModel();
        $existing = $trainerModel->find($id);
        if (!$existing) {
            flash('error', 'Trainer not found.');
            redirect('/admin/trainers');
        }
        
        $payload = $this->trainerPayload();
        $payload['image_path'] = (string) ($existing['image_path'] ?? '');
        
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $payload['image_path'] = $this->storeTrainerImage($_FILES['image'], (string) ($existing['image_path'] ?? ''));
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/admin/trainers');
                }
            }
        }
        
        $trainerModel->update($id, $payload);
        flash('success', 'Trainer updated.'); redirect('/admin/trainers');
    }

    public function deleteTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        $id = (int) $_POST['id'];
        $trainer = (new TrainerModel())->find($id);
        if ($trainer) {
            $this->removeTrainerImage((string) ($trainer['image_path'] ?? ''));
        }
        (new TrainerModel())->delete($id);
        flash('success', 'Trainer deleted.'); redirect('/admin/trainers');
    }

    private function trainerPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'specialty' => trim((string) $_POST['specialty']),
            'bio' => trim((string) $_POST['bio']),
            'image_path' => '',
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
        $payload = $this->locationPayload();
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $payload['image_path'] = $this->storeLocationImage($_FILES['image']);
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/admin/locations');
                }
            }
        }

        (new LocationModel())->create($payload);
        flash('success', 'Location created.'); redirect('/admin/locations');
    }

    public function updateLocation(): void
    {
        $this->requireAdmin(); verify_csrf();
        $id = (int) $_POST['id'];
        $locationModel = new LocationModel();
        $existing = $locationModel->find($id);
        if (!$existing) {
            flash('error', 'Location not found.');
            redirect('/admin/locations');
        }

        $payload = $this->locationPayload();
        $payload['image_path'] = (string) ($existing['image_path'] ?? '');

        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $payload['image_path'] = $this->storeLocationImage($_FILES['image'], (string) ($existing['image_path'] ?? ''));
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/admin/locations');
                }
            }
        }

        $locationModel->update($id, $payload);
        flash('success', 'Location updated.'); redirect('/admin/locations');
    }

    public function deleteLocation(): void
    {
        $this->requireAdmin(); verify_csrf();
        $id = (int) $_POST['id'];
        $location = (new LocationModel())->find($id);
        if ($location) {
            $this->removeLocationImage((string) ($location['image_path'] ?? ''));
        }
        (new LocationModel())->delete($id);
        flash('success', 'Location deleted.'); redirect('/admin/locations');
    }

    private function locationPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'address' => trim((string) $_POST['address']),
            'phone' => trim((string) $_POST['phone']),
            'opening_hours' => trim((string) $_POST['opening_hours']),
            'image_path' => '',
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

    private function storeTrainerImage(array $file, string $existingImagePath = ''): string
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Unable to upload image. Please try a different file.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > (5 * 1024 * 1024)) {
            throw new \RuntimeException('Trainer image must be under 5MB.');
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

        $relativeDirectory = '/assets/images/trainers';
        $absoluteDirectory = dirname(__DIR__, 2) . '/public' . $relativeDirectory;
        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new \RuntimeException('Unable to prepare trainer image directory.');
        }

        $fileName = 'trainer-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mimeType];
        $absolutePath = $absoluteDirectory . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new \RuntimeException('Failed to save uploaded image.');
        }

        $this->removeTrainerImage($existingImagePath);

        return $relativeDirectory . '/' . $fileName;
    }

    private function removeTrainerImage(string $relativePath): void
    {
        if ($relativePath === '' || !str_starts_with($relativePath, '/assets/images/trainers/')) {
            return;
        }

        $absolutePath = dirname(__DIR__, 2) . '/public' . $relativePath;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function storeLocationImage(array $file, string $existingImagePath = ''): string
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Unable to upload image. Please try a different file.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > (5 * 1024 * 1024)) {
            throw new \RuntimeException('Location image must be under 5MB.');
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

        $relativeDirectory = '/assets/images/locations';
        $absoluteDirectory = dirname(__DIR__, 2) . '/public' . $relativeDirectory;
        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new \RuntimeException('Unable to prepare location image directory.');
        }

        $fileName = 'location-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mimeType];
        $absolutePath = $absoluteDirectory . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new \RuntimeException('Failed to save uploaded image.');
        }

        $this->removeLocationImage($existingImagePath);

        return $relativeDirectory . '/' . $fileName;
    }

    private function removeLocationImage(string $relativePath): void
    {
        if ($relativePath === '' || !str_starts_with($relativePath, '/assets/images/locations/')) {
            return;
        }

        $absolutePath = dirname(__DIR__, 2) . '/public' . $relativePath;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}

