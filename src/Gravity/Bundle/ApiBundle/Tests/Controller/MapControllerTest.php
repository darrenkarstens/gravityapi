<?php

namespace Gravity\Bundle\ApiBundle\Tests\Controller;

class MapControllerTest extends CleanDatabaseWebTestCase {
	
	const TEST_ACCOUNT_NAME = 'webtest123';
	const TEST_MAP_TITLE = 'maptitle123';
	const TEST_SLOT_NUMBER = 1;
	const MAP_DATA = '[[4,4,2,4,4,4,4,4,4,4],[4,4,4,4,4,4,4,4,4,4],[4,4,4,4,6,6,6,4,4,4]]';
	
	static $testMapId = 0;
	
    public function test403WithNoUserAccountProvided() {
        $this->client->request('GET', '/api/map/list/all');
		$this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
	
    public function testUserAccountCreatedWhenProvidingUserAccountParameter() {
        $this->client->request('GET', '/api/map/list/all?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
	
    public function testSaveMapActionMissingMapGives404() {
        $this->client->request('POST', '/api/map/save?userAccount='.self::TEST_ACCOUNT_NAME.'&slot='.self::TEST_SLOT_NUMBER);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
    public function testSaveMapActionMissingSlotGives404() {
        $this->client->request('POST', '/api/map/save?userAccount='.self::TEST_ACCOUNT_NAME, array('map'=>self::MAP_DATA));
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
    public function testSaveMapActionCreatingNewMap() {
        $this->client->request('POST', '/api/map/save?userAccount='.self::TEST_ACCOUNT_NAME.'&slot='.self::TEST_SLOT_NUMBER, array('map'=>self::MAP_DATA));
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('Success', $jsonResponse->result);
	}
	
    public function testListMyActionShowsNewMap() {
        $this->client->request('GET', '/api/map/list/my?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals(1, count($jsonResponse->maps));
		$mapInfo = $jsonResponse->maps[0];
		self::$testMapId = $mapInfo->id;
		$this->assertEquals(0,$mapInfo->published);
		$this->assertEquals(self::TEST_SLOT_NUMBER, $mapInfo->slot);
	}
	
    public function testLoadMapActionWithoutMapIdGives404() {
        $this->client->request('GET', '/api/map/load?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
    public function testLoadMapActionWithInvalidMapIdGives404() {
        $this->client->request('GET', '/api/map/load?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId=F3E5');
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
    public function testLoadMapActionWithIncorrectMapIdGives404() {
        $this->client->request('GET', '/api/map/load?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId=35423423');
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
    public function testLoadMapActionWithCorrectMapId() {
        $this->client->request('GET', '/api/map/load?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::$testMapId);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$this->assertEquals(self::MAP_DATA, $this->client->getResponse()->getContent());
	}
	
	public function testPublishActionWithoutMapIdGives404() {
        $this->client->request('POST', '/api/map/publish?userAccount='.self::TEST_ACCOUNT_NAME.'&title='.self::TEST_MAP_TITLE);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
	public function testPublishActionWithoutTitleGives404() {
        $this->client->request('POST', '/api/map/publish?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::$testMapId);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
	public function testPublishActionPublishMap() {
        $this->client->request('POST', '/api/map/publish?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::$testMapId.'&title='.self::TEST_MAP_TITLE);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('Success', $jsonResponse->result);
	}
	
	public function testListAllActionShowsPublishedMap() {
        $this->client->request('GET', '/api/map/list/all?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals(1, count($jsonResponse->list));
		$row = $jsonResponse->list[0];
		$this->assertEquals(self::TEST_MAP_TITLE, $row->title);
		$this->assertEquals(self::$testMapId, $row->mapIds[self::TEST_SLOT_NUMBER]);
	}
	
	public function testUnpublishActionWithoutMapIdGives404() {
        $this->client->request('POST', '/api/map/unpublish?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
	public function testUnpublishActionUnpublishesMap() {
        $this->client->request('POST', '/api/map/unpublish?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::$testMapId);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('Success', $jsonResponse->result);
	}
	
	public function testListAllActionUnpublishedMapNoLongerShown() {
        $this->client->request('GET', '/api/map/list/all?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals(0, count($jsonResponse->list));
	}
}
