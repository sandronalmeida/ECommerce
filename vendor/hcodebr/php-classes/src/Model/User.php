<?php
namespace Hcode\Model;

Use \Hcode\DB\SqL;
Use \Hcode\Model;
class User extends ModeL{
	const SESSION="User";
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
	
	
}


?>