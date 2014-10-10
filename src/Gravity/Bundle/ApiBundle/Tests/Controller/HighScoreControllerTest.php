<?php

namespace Gravity\Bundle\ApiBundle\Tests\Controller;

class HighScoreControllerTest extends CleanDatabaseWebTestCase {
	
	const TEST_ACCOUNT_NAME = 'webtest123';
	const TEST_SCORE_TITLE = 'scoretitle123';
	const TEST_MAP_ID = 3;
	const SCORE_FIRST = 35000;
	const SCORE_SECOND = 40000;
	const SCORE_THIRD = 45000;
	
	public function testSetScoreTitleActionWithoutScoreTitleResponds404() {
		$this->client->request('POST', '/api/score/set_title?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
	public function testSetScoreTitleActionSettingTitle() {
		$this->client->request('POST', '/api/score/set_title?userAccount='.self::TEST_ACCOUNT_NAME.'&scoreTitle='.self::TEST_SCORE_TITLE);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('saved', $jsonResponse->result);
	}
	
	public function testSetActionWithoutMapIdResponds404() {
		$this->client->request('POST', '/api/score/set_title?userAccount='.self::TEST_ACCOUNT_NAME.'&score='.self::SCORE_SECOND);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
	public function testSetActionWithoutScoreResponds404() {
		$this->client->request('POST', '/api/score/set_title?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::TEST_MAP_ID);
		$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
	}
	
	public function testSetActionFirstScoreForMap() {
		$this->client->request('POST', '/api/score/set?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::TEST_MAP_ID.'&score='.self::SCORE_SECOND);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());		
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('newrecord', $jsonResponse->result);
	}
	
	public function testSetActionWorseScoreResultsInNoChange() {
		$this->client->request('POST', '/api/score/set?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::TEST_MAP_ID.'&score='.self::SCORE_THIRD);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('nochange', $jsonResponse->result);
	}
	
	public function testSetActionSameScoreResultsInNoChange() {
		$this->client->request('POST', '/api/score/set?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::TEST_MAP_ID.'&score='.self::SCORE_SECOND);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('nochange', $jsonResponse->result);
	}
	
	public function testSetActionBetterScoreResultsInNewRecord() {
		$this->client->request('POST', '/api/score/set?userAccount='.self::TEST_ACCOUNT_NAME.'&mapId='.self::TEST_MAP_ID.'&score='.self::SCORE_FIRST);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals('newrecord', $jsonResponse->result);
	}
	
	public function testListShowsHighScore() {
		$this->client->request('GET', '/api/score/list?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$this->assertEquals(1, count($jsonResponse->maps));
		$map = $jsonResponse->maps[0];
		$this->assertEquals(2, count($map));
		$highScore = $map[0];
		$this->assertEquals(0, $highScore->myscore);
		$this->assertEquals(self::SCORE_FIRST, $highScore->score);
		$this->assertEquals(self::TEST_MAP_ID, $highScore->mapId);
		$this->assertEquals(self::TEST_SCORE_TITLE, $highScore->scoreTitle);
	}
	
	public function testListShowsMyScore() {
		$this->client->request('GET', '/api/score/list?userAccount='.self::TEST_ACCOUNT_NAME);
		$this->assertEquals(200, $this->client->getResponse()->getStatusCode());
		$this->client->getResponse()->headers->contains('Content-Type','application/json');
		$jsonResponse = json_decode($this->client->getResponse()->getContent());
		$map = $jsonResponse->maps[0];
		$myScore = $map[1];
		$this->assertEquals(1, $myScore->myscore);
		$this->assertEquals(self::SCORE_FIRST, $myScore->score);
		$this->assertEquals(self::TEST_MAP_ID, $myScore->mapId);
		$this->assertEquals(self::TEST_SCORE_TITLE, $myScore->scoreTitle);
	}
	
}
