<?php
namespace Hcode\Model;
Use \Hcode\Mailer;
Use \Hcode\DB\SqL;
Use \Hcode\Model;
class Category extends ModeL{
	public static function listAll(){
		$sql = new SqL();
		return $sql->select('SELECT * FROM tb_categories ORDER BY descategory');		
	}
	
	public function save(){
		
		$sql = new SqL();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",array(
		":idcategory"=>$this->getidcategory(),
		":descategory"=>$this->getdescategory()				
		));
		$this->setData($results[0]);
	}
	
	public function get($idcategory){
		$sql = new SqL();
		$results= $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",array(
		":idcategory"=>$idcategory
		));	
		$this->setData($results[0]);		
	}

	public function delete(){
	$sql = new SqL();
	$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",array(
	":idcategory"=>$this->getidcategory()
	));			
	}

	
	/*
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
	*/
}
?>