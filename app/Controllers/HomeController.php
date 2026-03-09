<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\ContactMessageModel;
use App\Models\GymClassModel;
use App\Models\LocationModel;
use App\Models\MembershipPlanModel;
use App\Models\TrainerModel;

class HomeController extends Controller
{
    public function home(): void
    {
        $planModel = new MembershipPlanModel();
        $classModel = new GymClassModel();
        $trainerModel = new TrainerModel();
        $locationModel = new LocationModel();

        $this->render('pages/home', [
            'plans' => array_slice($planModel->activePlans(), 0, 3),
            'classes' => array_slice($classModel->upcomingActive(), 0, 3),
            'trainers' => array_slice($trainerModel->active(), 0, 3),
            'locations' => array_slice($locationModel->active(), 0, 2),
            'title' => 'Home',
        ]);
    }

    public function about(): void
    {
        $this->render('pages/about', ['title' => 'About Us']);
    }

    public function plans(): void
    {
        $planModel = new MembershipPlanModel();
        $this->render('pages/plans', ['plans' => $planModel->activePlans(), 'title' => 'Membership Plans']);
    }

    public function trainers(): void
    {
        $trainerModel = new TrainerModel();
        $this->render('pages/trainers', ['trainers' => $trainerModel->active(), 'title' => 'Trainers']);
    }

    public function schedule(): void
    {
        $classModel = new GymClassModel();
        $this->render('pages/schedule', ['classes' => $classModel->upcomingActive(), 'title' => 'Class Schedule']);
    }

    public function locations(): void
    {
        $locationModel = new LocationModel();
        $this->render('pages/locations', ['locations' => $locationModel->active(), 'title' => 'Gym Locations']);
    }

    public function contact(): void
    {
        $this->render('pages/contact', ['title' => 'Contact']);
    }

    public function submitContact(): void
    {
        verify_csrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        set_old($_POST);

        if (!Validator::required($name) || !Validator::required($subject) || !Validator::required($message) || !Validator::email($email)) {
            flash('error', 'Please provide valid contact details.');
            redirect('/contact');
        }

        $model = new ContactMessageModel();
        $model->create([
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
        ]);

        clear_old();
        flash('success', 'Message submitted successfully. We will get back to you soon.');
        redirect('/contact');
    }

    public function faq(): void
    {
        $this->render('pages/faq', ['title' => 'FAQ']);
    }
}
