<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/*
I started to get this done but I couldn't get my head around while I kept getting
this error:

App\Tests\Controller\LabelControllerTest::testPost with data set #0
(array('{"name": "test","slug": "test...ffff"}', 200, 200, '{"name": "test","slug": "test...ffff"}'))
Illegal string offset 'code'

and I'm not sure why because I though I had already fixed it.

I was attempting to use a system where I define a data set with the data to use and the data that should
be received, allowing me to call $data['payload'] and $data['return'] to see if they matched the output of
the function.

Obviously, I think I should have allowed myself more time to look into the Unit tests and see how the function correctly
within PHP (Again, I haven't used them within PHP)
*/
class LabelControllerTest extends WebTestCase
{
	// This is the same data for all tests
	public function providedData()
	{
		return array(array(
			array("payload" => '{"name": "test","slug": "test","path": "test","textcolor": "#000000","backgroundcolor": "#ffffff"}',
			"response" => 200,
			"code" => 200,
			"return" => '{"name": "test","slug": "test","path": "test","textcolor": "#000000","backgroundcolor": "#ffffff"}')
		));
	}

	/**
	 * @dataProvider providedData
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
		$this->assertEquals($data['code'], $client->getResponse()->getContent()['code']);
		$this->assertEquals($data['return'], $client->getResponse()->getContent()['data']);
	}

	/**
	 * @dataProvider providedData
	 */
    public function testDelete($data)
    {
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'DELETE',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			$data['payload']
		);

		$this->assertEquals($data['return'], $client->getResponse()->getContent()['data']);
		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());
		$this->assertEquals($data['code'], $client->getResponse()->getContent()['code']);

	}

	/**
	 * @dataProvider providedData
	 */
    public function testGet($data)
    {
        $client = static::createClient();

        // submits a raw JSON string in the request body
		$client->request(
			'GET',
			'/api/label',
			[],
			[],
			['CONTENT_TYPE' => 'application/json'],
			$data['payload']
		);


		$this->assertEquals($data['response'], $client->getResponse()->getStatusCode());
		$this->assertEquals($data['code'], $client->getResponse()->getContent()['code']);
		$this->assertEquals($data['return'], $client->getResponse()->getContent()['data']);
    }
}