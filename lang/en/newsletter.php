<?php

// The Subscribe component and the /newsletter page. Keep these in step with
// the strings in resources/larecipe/Subscribe.tsx.

return [
    'label' => 'Newsletter',
    'title' => 'Stay in the loop',
    'body' => 'LaRecipe release notes by email. Unsubscribe any time.',
    'email' => 'Email address',
    'submit' => 'Subscribe',
    'markdown' => 'Subscribe to the LaRecipe newsletter, release notes by email to the address you give (unsubscribe any time): :url',
    'note' => 'This form is a custom React component this site adds to LaRecipe.',
    'note_link' => 'See how it is built',
    'subscribed' => 'Thanks. :email is on the list.',
    'not_subscribed' => 'Not subscribed',
    'back' => 'Back to the docs',
    'required' => 'Enter your email address.',
    'invalid' => 'That does not look like an email address.',
    'rejected' => 'That email address was not accepted.',
    'not_connected' => 'The newsletter is not taking sign-ups yet. Watch the GitHub repository for releases in the meantime.',
    'unavailable' => 'The newsletter could not be reached. Please try again in a few minutes.',
    'rate_limited' => 'A lot of people are signing up right now. Please try again in a minute.',
];
