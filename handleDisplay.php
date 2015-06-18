<?php

/**
* Handles display of various data
*/
class displayHandler{
	private $host;
	private $port;
	private $url;
	
	function __construct(){
		$this->host = 'localhost';
		$this->port = '27017';
//		$this->url = "mongodb://$this->host:$this->port";
		$this->url="mongodb://Aashish:ashrock1993.mongolab.com:27457/finaldojugaad";
	}

	/**
	 *	Gets all the data sorted by timestamp
	 *	@param : NA
	 *	@return : Json containing the data sorted by timestamp
	 */
	public function getAllData(){
//		echo "Inside getAllData\n";
		$client = new Mongo($this->url);
		if ($client == null) {
			echo "Failed to create a client\n";
			return null;
		}
//		echo "Client created\n";
		$db = $client->finaldojugaad;
		if ($db == null) {
			echo "Database not found\n";
			return null;
		}
		$collection = $db->posts;
		if ($collection != null) {
			$cursor = $collection->find();
			$cursor->sort(array('timestamp'=>-1));
			$numrecords = $cursor->count();
			$postsarray = array();
			$i = 0;
			foreach ($cursor as $doc) {
//				print_r($doc['comments']);
				$postsarray[$i] = $doc;
				$i++;
			}
//			$postsjson = json_encode($postsarray);
			return $postsarray;
		}
		echo "Collection not found\n";
		return null;
	}


	/**
	 *
	 *	Function returns the recent posts by users based on the
	 *	recent comments or post timestamps.
	 *	@param : emailid of user to uniquely identify user
	 *	@return : json containing all of the user's activities, 
	 *			sorted according to the timestamp of recent activity.
	 *
	 */
	public function getUserFeed($useremail){
		$client = new MongoClient();
		if ($client == null) {
			echo "Failed to create a client\n";
			return null;
		}

		$db = $client->finaldojugaad;
		if ($db == null) {
			echo "Database not found\n";
			return null;
		}
		$collection = $db->posts;
		if ($collection != null) {
			$cursor = $collection->find("{'useremail':$useremail}");
			$numrecords = $cursor->count();
			$postsarray = array();
			$i = 0;
			foreach ($cursor as $doc) {
				$postsarray[$i] = $doc;
				$i++;
			}
			usort($postsarray, function($rec1, $rec2){
				$recentTS1 = $rec1['timestamp'];
				$recentTS2 = $rec2['timestamp'];
				$numcom1 = count($rec1['comments']);
				$numcom2 = count($rec2['comments']);
				for ($i=0; $i < $numcom1; $i++) { 
					if ($rec1['comments'][$i]['commentedAt'] > $recentTS1) {
						$recentTS1 = $rec1['comments'][$i]['commentedAt'];
					}
				}
				for ($i=0; $i < $numcom2; $i++) { 
					if ($rec2['comments'][$i]['commentedAt'] > $recentTS2) {
						$recentTS2 = $rec2['comments'][$i]['commentedAt'];
					}
				}
				return $recentTS1 - $recentTS2;
			});
			$postsjson = json_encode($postsarray);
			return $postsjson;	
		}
		else{
			echo "Collection not found\n";
			return null;
		}
	}


	/**
	 *	Function returns the user's posts according 
	 *	their post timestamp. This sorting does not take
	 *	activities on those posts into account
	 *	@param : email id of user
	 *	@return : json containing the user's posts
	 */
	public function getUserData($useremail){
		$client = new MongoClient();
		if ($client == null) {
			echo "Failed to create a client\n";
			return null;
		}

		$db = $client->finaldojugaad;
		if ($db == null) {
			echo "Database not found\n";
			return null;
		}
		$collection = $db->posts;
		if ($collection != null) {
			$cursor = $collection->find("{'useremail':$useremail}");
			$cursor->sort(array('timestamp'=>-1));
			$numrecords = $cursor->count();
			$postsarray = array();
			$i = 0;
			foreach ($cursor as $doc) {
				$postsarray[$i] = $doc;
				$i++;
			}
			$postsjson = json_encode($postsarray);
			return $postsjson;
		}
	}


	public function getTimeDiffString($from){
	    $now = time();
	    $diff = ($from > $now) ? $from - $now : $now - $from;
	    if ($diff >= 3600) {
	    	$levels = 1;
	    }
	    else{
	    	$levels = 2;
	    }
	    $status = ($from > $now) ? ' away' : ' ago';
	    $times = array(31536000, 2628000, 604800, 86400, 3600, 60, 1);
	    $words = array('year', 'month', 'week', 'day', 'hour', 'minute', 'second');
	    $str = array();
	    foreach ($times as $k=>$v){
	        $val = floor($diff/$v);
	        if ($val) {
	            $str[] = $val .' '. $words[$k] . ($val==1 ? '' : 's');
	            $levels--;
	        }
	        $diff %= $v;
	        if ($levels==0) break;
	    }
    	return implode(', ', $str) . $status;
	}
}




?>