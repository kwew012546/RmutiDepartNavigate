window.togglePassword = function(passwordInputId, iconId) {
  const passwordInput = document.getElementById(passwordInputId);
  const toggleIcon = document.getElementById(iconId);

  if (passwordInput.type === "password") {
      passwordInput.type = "text";
      toggleIcon.classList.remove("fa-eye-slash");
      toggleIcon.classList.add("fa-eye");
  } else {
      passwordInput.type = "password";
      toggleIcon.classList.remove("fa-eye");
      toggleIcon.classList.add("fa-eye-slash");
  }
}

$(document).ready(function() {
  $('#registerform').on('submit', function(e) {
      e.preventDefault();
      var formData = new FormData(this);
      $.ajax({
          url: 'controller/insert_user.php',
          method: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
              console.log(response);
              $('#registerform')[0].reset();
          },
          error: function() {
              alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
          }
      });
  });
});

$(document).ready(function() {
  $('#loginform').on('submit', function(e) {
      e.preventDefault();
      var formData = new FormData(this);
      $.ajax({
          url: 'controller/login_user.php',
          method: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
              console.log(response);
              if (response.trim() === 'เข้าสู่ระบบสําเร็จ!') {
                $('#loginform')[0].reset();
                window.location.href = '../index.php';
              } else {
                document.getElementById("error").innerHTML = response;
                document.getElementById("user_code1").style = "border: 1px solid red;";
                document.getElementById("password1").style = "border: 1px solid red;";
              }
          },
          error: function() {
              alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
          }
      });
  });
}); 