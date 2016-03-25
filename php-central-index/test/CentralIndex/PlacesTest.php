<?php

    /**
     * Class CentralIndex_PlacesTest
     * Test Location: https://www.chamberofcommerce.com/santa-barbara-ca/47153381-local-market-launch
     */
    // Tread carefully. There is no sandbox environment.
    // The test goes to their production DB.
    // The test costs $0.025 so don't go wild homie.

    class CentralIndex_PlacesTest extends CentralIndexTestCase{

        public function addTestOrder($changePhone = false){
            $data = [
                // * indicates required
                'business_id' => '0000000000', // *, Int only, need to save or generate from uuid.
                'business_name' => 'Affordable Storage Containers', // *
                'user_email'    => 'affordabledave@qwestoffice.net', // *
                'address_line 1' => '2308 Milwaukee Way',
                'address_line 2' => '',
                'city' => 'Tacoma', // *
                'state' => 'WA', //*
                'zip_code' => '98421', //*
                'country' => 'US',
                'latitude' => 47.2456996, //*
                'longitude' => -122.4007040, //*
                'phone' => ($changePhone) ? '8058843882' : '8008843882', // 8008843882 is real one
                'website' => 'http://www.affordablecontainers.com/',
                'description' => 'Welcome to Affordable Storage Containers!',
                'category' => 'Storage Containers, Facilities & Warehouses',
            ];
            try {
                // if changePhone is true then its an update
                if($changePhone){
                    $update = CentralIndex_ApiLml::update($data);
                    return $update->getData();
                }else{
                    $order = CentralIndex_ApiLml::order($data);
                    return $order = $order->getData();
                }

                //var_dump($order);

            } catch (Exception $e) {
                var_dump( $e->getCode() . " ERROR " . $e->getMessage());
                var_dump($e->getJsonBody());
            }
        }

        public function testPlaceNewOrder(){
            $response = $this->addTestOrder();
            $this->assertArrayHasKey('code',$response);
            $this->assertArrayHasKey('body',$response);
            $this->assertArrayHasKey('status',$response['body']);
            $this->assertArrayHasKey('id',$response['body']);
            $this->assertArrayHasKey('url',$response['body']);
            $this->assertArrayNotHasKey('errors',$response['body']);

            $this->assertInternalType('int',$response['body']['id']);
            $this->assertInternalType('string',$response['body']['url']);
            $this->assertGreaterThan(1,strlen($response['body']['url']));
            $this->assertEquals('LIVE',$response['body']['status']);
            $this->assertEquals(200, $response['code']);
        }


        public function testUpdateOrder(){
            // Add Order if it doesnt exist
            $this->addTestOrder();
            // Alter the phone number
            $response = $this->addTestOrder(true);
            // ?????
            // Profit
            $this->assertArrayHasKey('code',$response);
            $this->assertArrayHasKey('body',$response);
            $this->assertArrayHasKey('status',$response['body']);
            $this->assertArrayHasKey('id',$response['body']);
            $this->assertArrayHasKey('url',$response['body']);
            $this->assertArrayNotHasKey('errors',$response['body']);

            $this->assertInternalType('int',$response['body']['id']);
            $this->assertInternalType('string',$response['body']['url']);
            $this->assertGreaterThan(1,strlen($response['body']['url']));
            $this->assertEquals('LIVE',$response['body']['status']);
            $this->assertEquals(200, $response['code']);

        }

        public function testDeleteLocation(){
            // Add Order if it doesnt exist
            $this->addTestOrder();

            $data = [];
            $data['business_id'] = '0000000000';

            // Now lets delete it!
            try{
                $order = CentralIndex_ApiLml::cancel($data);
                $response = $order->getData();
                var_dump($response);

                $this->assertArrayHasKey('code',$response);
                $this->assertArrayHasKey('body',$response);
                $this->assertArrayHasKey('id',$response['body']);
                $this->assertArrayNotHasKey('errors',$response['body']);

                $this->assertInternalType('int',$response['body']['id']);
                $this->assertEquals(200, $response['code']);


            } catch (Exception $e) {
                var_dump( $e->getCode() . " ERROR " . $e->getMessage());
                var_dump($e->getJsonBody());
            }

        }

    }