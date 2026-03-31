<?php

declare(strict_types=1);

return [
    ['GET', '/', 'HomeController', 'home', 'public'],
    ['GET', '/about', 'HomeController', 'about', 'public'],
    ['GET', '/plans', 'HomeController', 'plans', 'public'],
    ['GET', '/trainers', 'HomeController', 'trainers', 'public'],
    ['GET', '/schedule', 'HomeController', 'schedule', 'public'],
    ['GET', '/locations', 'HomeController', 'locations', 'public'],
    ['GET', '/contact', 'HomeController', 'contact', 'public'],
    ['POST', '/contact', 'HomeController', 'submitContact', 'public'],
    ['GET', '/faq', 'HomeController', 'faq', 'public'],

    ['GET', '/register', 'AuthController', 'showRegister', 'guest'],
    ['POST', '/register', 'AuthController', 'register', 'guest'],
    ['GET', '/login', 'AuthController', 'showLogin', 'guest'],
    ['POST', '/login', 'AuthController', 'login', 'guest'],
    ['GET', '/admin/login', 'AuthController', 'showAdminLogin', 'guest'],
    ['POST', '/admin/login', 'AuthController', 'adminLogin', 'guest'],
    ['POST', '/logout', 'AuthController', 'logout', 'auth'],

    ['GET', '/member/dashboard', 'MemberController', 'dashboard', 'member'],
    ['GET', '/member/profile', 'MemberController', 'profile', 'member'],
    ['POST', '/member/profile', 'MemberController', 'updateProfile', 'member'],
    ['POST', '/member/membership/subscribe', 'MemberController', 'subscribe', 'member'],
    ['POST', '/member/membership/renew', 'MemberController', 'renew', 'member'],
    ['POST', '/member/membership/cancel', 'MemberController', 'cancelMembership', 'member'],
    ['GET', '/member/bookings', 'MemberController', 'bookings', 'member'],
    ['GET', '/member/invoices/download', 'MemberController', 'downloadInvoice', 'member'],
    ['POST', '/member/bookings/book', 'MemberController', 'bookClass', 'member'],
    ['POST', '/member/bookings/cancel', 'MemberController', 'cancelBooking', 'member'],
    ['POST', '/member/bookings/waitlist/cancel', 'MemberController', 'cancelWaitlist', 'member'],
    ['POST', '/member/payments/checkout', 'PaymentController', 'checkout', 'member'],
    ['POST', '/member/payments/resume', 'PaymentController', 'resumeCheckout', 'member'],

    ['POST', '/webhooks/stripe', 'PaymentController', 'stripeWebhook', 'public'],

    ['GET', '/admin/dashboard', 'AdminController', 'dashboard', 'admin'],
    ['GET', '/admin/users', 'AdminController', 'users', 'admin'],
    ['POST', '/admin/users/create', 'AdminController', 'createUser', 'admin'],
    ['POST', '/admin/users/update', 'AdminController', 'updateUser', 'admin'],
    ['POST', '/admin/users/delete', 'AdminController', 'deleteUser', 'admin'],

    ['GET', '/admin/plans', 'AdminController', 'plans', 'admin'],
    ['POST', '/admin/plans/create', 'AdminController', 'createPlan', 'admin'],
    ['POST', '/admin/plans/update', 'AdminController', 'updatePlan', 'admin'],
    ['POST', '/admin/plans/delete', 'AdminController', 'deletePlan', 'admin'],

    ['GET', '/admin/trainers', 'AdminController', 'trainers', 'admin'],
    ['POST', '/admin/trainers/create', 'AdminController', 'createTrainer', 'admin'],
    ['POST', '/admin/trainers/update', 'AdminController', 'updateTrainer', 'admin'],
    ['POST', '/admin/trainers/delete', 'AdminController', 'deleteTrainer', 'admin'],

    ['GET', '/admin/classes', 'AdminController', 'classes', 'admin'],
    ['POST', '/admin/classes/create', 'AdminController', 'createClass', 'admin'],
    ['POST', '/admin/classes/update', 'AdminController', 'updateClass', 'admin'],
    ['POST', '/admin/classes/delete', 'AdminController', 'deleteClass', 'admin'],

    ['GET', '/admin/locations', 'AdminController', 'locations', 'admin'],
    ['POST', '/admin/locations/create', 'AdminController', 'createLocation', 'admin'],
    ['POST', '/admin/locations/update', 'AdminController', 'updateLocation', 'admin'],
    ['POST', '/admin/locations/delete', 'AdminController', 'deleteLocation', 'admin'],

    ['GET', '/admin/bookings', 'AdminController', 'bookings', 'admin'],
    ['POST', '/admin/bookings/update', 'AdminController', 'updateBooking', 'admin'],
    ['POST', '/admin/bookings/delete', 'AdminController', 'deleteBooking', 'admin'],

    ['GET', '/admin/messages', 'AdminController', 'messages', 'admin'],
    ['POST', '/admin/messages/delete', 'AdminController', 'deleteMessage', 'admin'],
];
