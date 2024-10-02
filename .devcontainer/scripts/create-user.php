<?php

require_once __DIR__ . '/../../../../config/config.inc.php';

$firstname = 'John';
$lastname = 'Doe';
$email = 'shop-user@stancer.com';

if (!count(Customer::getCustomersByEmail($email))) {
    $customer = new Customer();

    $customer->firstname = $firstname;
    $customer->lastname = $lastname;
    $customer->email = $email;
    $customer->passwd = md5(_COOKIE_KEY_ . 'shop-user');

    $customer->save();

    $address = new Address();

    $address->alias = 'Home';
    $address->firstname = $firstname;
    $address->lastname = $lastname;
    $address->address1 = '42 John DOE street';
    $address->postcode = '99999';
    $address->city = 'CITY';
    $address->id_customer = $customer->id;
    $address->id_country = Country::getByIso('fr');

    $address->save();
}
