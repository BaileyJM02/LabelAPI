<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LabelControllerTest extends WebTestCase
{
	public function providePost()
	{
		return array(array(
			array("payload" => '{"name": "test","slug": "test","path": "test","textcolor": "#000000","backgroundcolor": "#ffffff"}',
			"response" => 200,
			"code" => 200,
			"return" => '{"name": "test","slug": "test","path": "test","textcolor": "#000000","backgroundcolor": "#ffffff"}')
		));
	}
	/**
	 * @dataProvider providePost
	 */
    public function testPost($data)
    {
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'POST',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			$data['payload']
		);


		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());
		$this->assertEquals($data['code'], $client->getResponse()->getContent()->code);
		$this->assertEquals($data['return'], $client->getResponse()->getContent()->data);
    }
}