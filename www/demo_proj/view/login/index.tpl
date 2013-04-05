<?php echo $objData; ?>
<div class="login"><img src="../images/logo.png" alt="Bardot - Hair Body Soul" id="logo" />
      
   <div class="form">
      <label>LOGIN <input type="text"     name="usrName" id="usrName" class="inputText" /></label><br>
      <label>SENHA <input type="password" name="usrPwd"  id="usrPwd"  class="inputText" /></label><br>
      <input type="submit" value="Enviar" name="loginSubmit" class="loginSubmit" /><br>
      <div class="errMsg"></div>
   </div>
   
   
</div>
<script type="text/javascript">
	$(function(){
			$.login_begin.call();
		});
</script>