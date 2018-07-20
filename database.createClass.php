<?php

define('DB_PACKET','today_20072018_');
define('DB_DSN', 'mysql:host=localhost;');
define('DB_DRIVER',array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
define('DB_USR', 'root');
define('DB_PWD', '');

class createDB{

	private $db,
			$state_create,
			$db_is_have_connect,
			$db_table_is_have = false,
			$db_table_name;

	public function __construct(){
		try{
			$this->db = new PDO(DB_DSN,DB_USR,DB_PWD,DB_DRIVER);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e){
			echo $e->getMessage();
			die();
		}
	
	}

	public function createOrConnect($c_db = null){
		if($c_db){
				
				$sql = "CREATE DATABASE IF NOT EXISTS ".DB_PACKET.$c_db;
				$back = $this->db->exec($sql);
				$this->is_database($c_db);	
				$this->state_create = $back ? true : false;
		}
		return $this;
	}

	public function is_database($db_name){
		try{
			$this->db->exec('use '.DB_PACKET.$db_name);
			$this->db_is_have_connect = true;
		}catch(PDOException $e){
			$this->db_is_have_connect = false;	
		}
		return $this->db_is_have_connect;
	}

	public function createTable($table_name = null){
		if($table_name && $this->db_is_have_connect){
			$table = "TABLE_20072018_".$table_name;
			$this->db_table_name = $table;
			$sql = "CREATE TABLE IF NOT EXISTS $table (
				    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				    firstname VARCHAR(30) NOT NULL,
				    lastname VARCHAR(30) NOT NULL,
				    email VARCHAR(50),
				    reg_date TIMESTAMP
				    )";

			try{
				$response = $this->db->exec($sql);
			}catch(PDOException $e){
				echo $e->getMessage();
				die();
			}	    
		}else{
			die('create table error');
		}
		
		return true;
	}

	public function register($firstname,$lastname,$email){
		
		$add = $this->db->prepare('INSERT INTO '.$this->db_table_name.' SET firstname = :fn, lastname = :ln , email = :em');

		$sonuc = $add->execute(array("fn"=>$firstname,
							"ln"=>$lastname,
							"em"=>$email));
	}

	public function fetchAll(){
		$query = $this->db->query('SELECT * FROM '.$this->db_table_name);
		$data = $query->fetchAll(PDO::FETCH_ASSOC);
		$d["rowCount"] = $query->rowCount();
		$d[] = $data;
		return $query->rowCount() ? $d : false;
		
	}
	public function removeRow($id){
		$remove = $this->db->prepare('DELETE FROM '.$this->db_table_name.' WHERE id = :id');
		return $remove->execute(["id"=>$id]);

	}
	public function __destruct(){
		$this->db = null;
	}




}

$db_name = "x";
$db_table_name = "sda";

$run = new createDB;

if($run->createOrConnect($db_name)->createTable($db_table_name)):
	if(isset($_POST) && !empty($_POST["firstname"])){
		$run->register($_POST["firstname"],$_POST["lastname"],$_POST["email"]);
	}

	if(isset($_GET) && !empty($_GET["id"])){
	  $run->removeRow($_GET["id"]);
	}


?>
<form action="" method="POST">
	<input type="text" name="firstname" placeholder="firstname">
	<input type="text" name="lastname" placeholder="lastname">
	<input type="text" name="email" placeholder="email">
	<input type="submit">
</form>
	<?php if($run->fetchAll()['rowCount'] > 0): ?>
	<table>
		<thead>
			<tr>
				<th>firstname</th>
				<th>lastname</th>
				<th>email</th>
				<th>remove</th>
			</tr>
		</thead>
		<tbody>
				<?php
				
				 foreach($run->fetchAll()[0] as $d): ?>
					<tr>
						<td><?=$d["firstname"]?></td>
						<td><?=$d["lastname"]?></td>
						<td><?=$d["email"]?></td>
						<td>
							<a href='?id=<?=$d["id"]?>'>DELETE</a>
						</td>
					</tr>

				<?php endforeach; ?>
			
		</tbody>
	</table>
	<?php endif; ?> 

<?php
endif;



