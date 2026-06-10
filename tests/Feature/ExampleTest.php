<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertDontSee(__('Sign up'), false);
    $response->assertDontSee(__('Register'), false);
});
