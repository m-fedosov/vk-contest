<?php

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class RegistrationControllerTest extends ApiTestCase
{
    public function testApiRegister(): void
    {
        static::createClient()->request('POST', '/api/register', ['json' => [
            'email' => 'testemail123@gmail.com',
            'password' => 'test123'
        ]]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['message' => 'Registered Successfully']);
    }
}
