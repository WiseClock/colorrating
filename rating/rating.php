<?php
class rating{

	public $average = 0;
	public $votes;
	public $status;
	public $table;
	public $itemId;
	private $path;
	
	function __construct($table,$itemId){
		try{
			if (isset($_GET['itemId'])) {
				$itemId = $_GET['itemId'];
			}
			$pathinfo = pathinfo(__FILE__);
			$this->path = realpath($pathinfo['dirname']) . "/database/ratings.sqlite";
			$dbh = new PDO("sqlite:$this->path");
			$this->table = $dbh->quote($table);
			// check if table needs to be created
			$table_check = $dbh->query("SELECT * FROM $this->table WHERE itemId='$itemId'");
			if(!$table_check){
				// create database table
				$dbh->query("CREATE TABLE $this->table (id INTEGER PRIMARY KEY, rating FLOAT(3,2), ip VARCHAR(15), itemId VARCHAR(255))");
				$dbh->query("INSERT INTO $this->table (rating, ip, itemId) VALUES (0, 'master', '$itemId')");
			} else {
				$this->average = $table_check->fetchColumn(1);
			}
			$this->votes = ($dbh->query("SELECT COUNT(*) FROM $this->table WHERE itemId='$itemId' AND ip <> 'master'")->fetchColumn());
		}catch( PDOException $exception ){
				die($exception->getMessage());
		}
		$dbh = NULL;		
	}

	function set_score($score, $ip, $itemId){
		try{
			$itemId = $_GET['itemId'];
			$dbh = new PDO("sqlite:$this->path");
			$voted = $dbh->query("SELECT id FROM $this->table WHERE ip='$ip' AND itemId='$itemId'");
			if(sizeof($voted->fetchAll())==0){
				$table_check2 = $dbh->query("SELECT * FROM $this->table WHERE itemId='$itemId' AND ip='master'");
				if ($table_check2->fetchColumn() == 0) {
					$dbh->query("INSERT INTO $this->table (rating, ip, itemId) VALUES (0, 'master', '$itemId')");
					
				}
				$dbh->query("INSERT INTO $this->table (rating, ip, itemId) VALUES ($score, '$ip', '$itemId')");
				$this->votes++;
				
				//cache average in the master row
				$statement = $dbh->query("SELECT rating FROM $this->table WHERE itemId='$itemId'");
				$total = $quantity = 0;
				$row = $statement->fetch(); //skip the master row
				while($row = $statement->fetch()){
					$total = $total + $row[0];
					$quantity++;
				}
				$this->average = round((($total*20)/$quantity),0);
				$statement = $dbh->query("UPDATE $this->table SET rating = $this->average WHERE ip='master' AND itemId='$itemId'");
				$this->votes = ($dbh->query("SELECT COUNT(*) FROM $this->table WHERE itemId='$itemId' AND ip <> 'master'")->fetchColumn());
				$this->status = '(thanks!)';
			} else {
				$this->status = '(already rated)';
			}
			
		}catch( PDOException $exception ){
				die($exception->getMessage());
		}
		$dbh = NULL;
	}
	
	function highest_rated($table,$numTop) {
		try{
			$ip = $_SERVER["REMOTE_ADDR"];
			$dbh = new PDO("sqlite:$this->path");
			$this->toprated = ($dbh->query("SELECT itemId,MAX(rating) AS rating,COUNT(rating)-1 AS votes FROM $this->table GROUP BY itemId ORDER BY MAX(rating) DESC LIMIT $numTop")->fetchAll(PDO::FETCH_ASSOC));
		}catch( PDOException $exception) {
			die($exception->getMessage());
		}
		$dbh = NULL;
	}
}

function rating_form($table,$itemId){
	$ip = $_SERVER["REMOTE_ADDR"];
	if(!isset($table) && isset($_GET['table'])){
		$table = $_GET['table'];
	}
	$rating = new rating($table,$itemId);
	$status = "<div class='score'>
				<a class='score1' href='?score=1&amp;table=$table&amp;user=$ip&itemId=$itemId'>1</a>
				<a class='score2' href='?score=2&amp;table=$table&amp;user=$ip&itemId=$itemId'>2</a>
				<a class='score3' href='?score=3&amp;table=$table&amp;user=$ip&itemId=$itemId'>3</a>
				<a class='score4' href='?score=4&amp;table=$table&amp;user=$ip&itemId=$itemId'>4</a>
				<a class='score5' href='?score=5&amp;table=$table&amp;user=$ip&itemId=$itemId'>5</a>
			</div>
	";
	if(isset($_GET['score'])){
		$score = $_GET['score'];
		if(is_numeric($score) && $score <=5 && $score >=1 && ($table==$_GET['table']) && isset($_GET["user"]) && $ip==$_GET["user"]){
			$rating->set_score($score, $ip, $itemId);
			$status = $rating->status;
		}
	}
	if(!isset($_GET['update'])){ echo "<div class='rating_wrapper'>"; }
	if ($rating->average == "") {
		$rating->average = 0;
	}
	?>
	<div class="sp_rating">
		<div class="rating">Rating:</div>
		<div class="base"><div class="average" style="width:<?php echo $rating->average; ?>%"><?php echo $rating->average; ?></div></div>
		<div class="votes"><?php echo $rating->votes; ?> votes</div>
		<div class="status">
			<?php echo $status; ?>
		</div>
	</div>
	<?php
	if(!isset($_GET['update'])){ echo "</div>"; }
}

function highest_rated($table,$numTop,$output=0) {
	if(!isset($table) && isset($_GET['table'])){
		$table = $_GET['table'];
	}
	$rating = new rating($table,0);
	$rating->highest_rated($table,$numTop);
	if ($output != 0) {
		print "<div class=\"sp_rating\">";
		$i=0;
		foreach ($rating->toprated as $row){ 
		?>
		<div class='rating_wrapper'>
			<div class="rating">Rating:</div>
			<div class="base"><div class="average" style="width:<?=$row['rating']?>%"><?=$row['rating']?></div></div>
			<div class="votes"><?=$row['votes']?> votes</div>
		</div>
		<?php }
		print "</div>";
	}
	else {
		return $rating->toprated;
	}
}

if(isset($_GET['update'])&&isset($_GET['table'])&&isset($_GET['itemId'])){
	rating_form($_GET['table'],$GET['itemId']);
}
?>