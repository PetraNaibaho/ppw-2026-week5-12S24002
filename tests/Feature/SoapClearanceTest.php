<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoapClearanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_wsdl_endpoint_returns_xml_contract(): void
    {
        $response = $this->get('/soap/sipus.wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('LibrarySoapService', $response->getContent());
        $this->assertStringContainsString('CheckClearanceStatus', $response->getContent());
    }

    public function test_soap_clearance_success_when_cleared(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S22001</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xml);

        $response->assertStatus(200);
        $this->assertStringContainsString('<m:is_cleared>true</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>0</m:active_loans>', $response->getContent());
    }

    public function test_soap_clearance_when_has_active_loans(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/clearance">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24002</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xml);

        $response->assertStatus(200);
        $this->assertStringContainsString('<m:is_cleared>false</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>1</m:active_loans>', $response->getContent());
    }

    public function test_soap_returns_fault_on_empty_body_or_invalid_xml(): void
    {
        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], 'invalid xml string');

        $response->assertStatus(500);
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $response->getContent());
    }
}
