<?php
namespace Hcode\Model;
Use \Hcode\Mailer;
Use \Hcode\DB\SqL;
Use \Hcode\Model;
class User extends ModeL{
	const SESSION="User";
	const SECRET ="Hcodephp7_Secret";
	public static function login($login, $password){
	$sql = new SqL();
	$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
		":LOGIN"=>$login));
		
	if(count($results) === 0)
	{
		// \indica para ir chamar o try principal da página, pois no namespace MODEL NÃO TEM UM TRY
	 throw new \Exception("Usuário Inexistente ou Senha Incorreta");	
	}		
	$data= $results[0];
	if(password_verify($password,$data["despassword"]) === true){
		$user = new User();
		$user->setData($data);
		$_SESSION[User::SESSION]=$user->getValues();
		return $user;
	}else{
		throw new \Exception("Usuário Inexistente ou Senha Incorreta");	
	}	
	}

	public static function verifyLogin($inadmin = true){
			if(
			!isset($_SESSION[User::SESSION])
			|| 
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(booL)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
			){
			  header("Location: /admin/login");
			  exit;			  
			}				
	}
		
	public static function logout(){
				$_SESSION[User::SESSION]=NULL;
	}
	
	public static function listAll(){
		$sql = new SqL();
		return $sql->select('SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson');		
	}
	
	public function save(){
		
		$sql = new SqL();
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone,:inadmin)",array(
		":desperson"=>$this->getdesperson(),
		":deslogin"=>$this->getdeslogin(),
		":despassword"=>$this->getdespassword(),
		":desemail"=>$this->getdesemail(),
		":nrphone"=>$this->getnrphone(),
		":inadmin"=>$this->getinadmin()		
		));
		$this->setData($results[0]);
	}
	
	public function get($iduser){
		$sql = new SqL();
		$results= $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(
		":iduser"=>$iduser
		));	
		$this->setData($results[0]);		
	}
	public function update(){				
		$sql = new SqL();
		$results = $sql->select("CALL sp_usersupdate_save(:iduser,:desperson, :deslogin, :despassword, :desemail, :nrphone,:inadmin)",array(
		":iduser"=>$this->getiduser(),
		":desperson"=>$this->getdesperson(),
		":deslogin"=>$this->getdeslogin(),
		":despassword"=>$this->getdespassword(),
		":desemail"=>$this->getdesemail(),
		":nrphone"=>$this->getnrphone(),
		":inadmin"=>$this->getinadmin()		
		));
		$this->setData($results[0]);		
	}
	public function delete(){
	$sql = new SqL();
	$sql->query("CALL sp_users_delete(:iduser)",array(
	":iduser"=>$this->getiduser()
	));			
	}
	public static function getForgot($email){
		$sql = new SqL();
		$results =$sql->select("SELECT * FROM tb_users A INNER JOIN tb_persons B USING(idperson) WHERE b.desemail =:email", array(
		":email"=> $email
		));
		if(count($results) === 0){
			throw new \Exception("Não Foi Possível Recuperar a Senha");
		}else{
			$data = $results[0];
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)", array(
			":iduser"=>$data["iduser"],
			":desip"=>$_SERVER["REMOTE_ADDR"]
			));
			if(count($results2) === 0){
				throw new \Exception("Não Foi Possível Recuperar a Senha");
			}else{
				$dataRecovery = $results2[0];
				$code= base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,User::SECRET,$dataRecovery["idrecovery"],MCRYPT_MODE_ECB));
				$link="http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				$mailer=new Mailer($data["desemail"],$data["desperson"],"Redefinir Senha Hcode Store","forgot",array(
				"name"=>$data["desperson"],
				"link"=>$link
				));
				$mailer->send();
				return $data;
				
			}
			
		}	
	}
	public static function validForgotDecrypt($code){
		$idrecovery= mcrypt_decrypt(MCRYPT_RIJNDAEL_128,User::SECRET,base64_encode($code),MCRYPT_MODE_ECB);
		$sql= new SqL();
		$results = $sql->select("SELECT * 
								 FROM tb_userspasswordrecoveries a
								 INNER JOIN tb_users b USING(iduser)								 
								 INNER JOIN tb_persons c USING(idperson)
								 WHERE 
									a.idrecovery = :idrecovery
									AND
									a.dtrecovery is null
									AND
									DATE_ADD(a.dtregister, INTERVAL  1 HOUR) >=	NOW();
								 ", array(":idrecovery"=>$idrecovery));
		if(count($results) === 0){
			throw new \Exception("Não Foi Possível Recuperar a Senha");
		}
		else{
			return $results[0];
		}
	}
	
	public static function setForgotUsed($idrecovery){
		$sql=new SqL();
		$sql->qyery("UPDATE tbuserspasswordrecoveries SET dtrecovery = :NOW() WHERE idrecovery = :idrecovery",array(
		":idrecovery"=>$idrecovery));		
	}
	public function setPassword($password){
		$sql = new SqL();
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser",array(
		"password"=>$password,
		"iduser"=>$this->getiduser()
		));
		
	}
}
?>