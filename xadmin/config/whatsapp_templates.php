<?php
/**
 * WhatsApp Template Name Configuration
 * 
 * Change the template names here to match what you created on Facebook/Meta.
 * The code uses the logical key (left side), and sends the actual template name (right side).
 * 
 * Example: If Facebook requires template name "service_update_1" for complaint registration,
 * just change: 'complaint_registered' => 'service_update_1'
 */

return [
    // When customer registers a complaint (web form or mobile app)
    // Parameters: {{1}} Ticket Number, {{2}} Category Name
    'complaint_registered' => 'complaint_registered_01',

    // When admin assigns complaint to a plumber (sent to plumber)
    // Parameters: {{1}} Ticket, {{2}} Category, {{3}} Customer Name, {{4}} Mobile, {{5}} Location, {{6}} Priority
    'complaint_assigned_plumber' => 'complaint_assigned_plumber_02',

    // When admin assigns complaint to a plumber (sent to customer)
    // Parameters: {{1}} Ticket, {{2}} Plumber Name, {{3}} Plumber Mobile
    'complaint_assigned_customer' => 'complaint_assigned_customer_03',
    

    // When admin cancels a complaint
    // Parameters: {{1}} Ticket, {{2}} Cancellation Reason
    'complaint_cancelled' => 'complaint_cancelled_04',

    // When plumber accepts complaint via mobile app
    // Parameters: {{1}} Ticket, {{2}} Plumber Name, {{3}} Plumber Mobile, {{4}} Visit DateTime
    'complaint_accepted' => 'complaint_accepted_05',

    // When plumber starts work via mobile app
    // Parameters: {{1}} Ticket, {{2}} Plumber Name
    'complaint_in_progress' => 'complaint_in_progress_06',

    // When plumber completes work - sends service code to customer
    // Parameters: {{1}} Ticket, {{2}} Total Cost, {{3}} Labor, {{4}} Parts, {{5}} Payment Mode, {{6}} Service Code
    'complaint_solved_servicecode' => 'complaint_solvedreference_07',

    // After OTP/service code verification - complaint closed
    // Parameters: {{1}} Ticket
    'complaint_closed_success' => 'complaint_closed_success_08',
];
