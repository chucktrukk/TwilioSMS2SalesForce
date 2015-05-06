<?php
// Load the questions we want: 
$question1 = 'Hello. Welcome to the event! We would like to ask you some questions about your experience.</Message><Message>What did you think of the venue & refreshments? 5 (Exceptional) 0 (Poor)'; // by adding the </Message><Message> you can break up the initial response into a welcome message and then question1.
$question2 = 'And the content of the Presentations? 5 (Exceptional) 0 (Poor)';
$question3 = 'How likely are you to attend future Twilio events from 5 (Definitely would) to 0 (definitely would not)';
$question4 = 'Is there anything specific you would like to discuss with Twilio? 5 (Yes, please asks someone to call) 0 (I’ll contact you if I need anything)';
// After we have all 4 questions we can upload to the DB and thank the user for their input
$endStatement = 'Thanks for your time. Hope you have a fun day!';

// If we have no cookies we need to set all the cookies to nil and ask the opening question.
if(!isset($_COOKIE['question1'])) {
    $TwiMLResponse = $question1;
    //setcookie('question1', 'nil');
    setcookie('event', $_POST['Body']);
	setcookie('question1', 'nil');
	setcookie('question2', 'nil');
	setcookie('question3', 'nil');
	setcookie('question4', 'nil');
}
// If Question 1 is blank we can pair the answer to question 1
elseif ($_COOKIE['question1'] == 'nil') {
	setcookie('question1', $_POST['Body']);
	$TwiMLResponse = $question2;
}
// If Question 1 is not blank we find out if question 2 is blank and move up the ladder
elseif (($_COOKIE['question2'] == 'nil')) {
	setcookie('question2', $_POST['Body']);
	$TwiMLResponse = $question3;	
}
elseif (($_COOKIE['question3'] == 'nil')) {
	setcookie('question3', $_POST['Body']);
	$TwiMLResponse = $question4;	
}
// After we get the response for question 4, we can assign it to the question. 
// Now we have all 4 questions answered and can pass the thank you note and also make a HTTP POST to our end point
elseif (($_COOKIE['question4'] == 'nil')) {
	// With the last question answered, we can reply with our end statement and POST all the data from the conversation.
	$TwiMLResponse = $endStatement;	
	// So now we have the cookies for the event and questions 1 to 3 and the BODY tag for answer 4. Now we can make a POST request to our form with that data.
	
	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_URL => 'https://www.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8',
	    CURLOPT_USERAGENT => 'TwilioSMS',
	    CURLOPT_POST => 1,
	    // POST fields for salesforce input:
	    CURLOPT_POSTFIELDS => array('oid' => 'ABC123', 'phone' => $_POST['From'], 'Campaign_ID' => $_COOKIE['event'], 'Question1' => $_COOKIE['question1'], 'Question2' => $_COOKIE['question2'], 'Question3' => $_COOKIE['question3'], 'Question4' => $_POST['Body'])
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);
}
header('content-type: text/xml');
?>


<Response><Message><?php echo $TwiMLResponse; ?></Message></Response>
