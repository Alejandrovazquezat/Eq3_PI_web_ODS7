document.getElementById("loginForm").addEventListener("submit", function(e){

    let email = document.querySelector("input[name='email']").value;
    let pass = document.querySelector("input[name='password']").value;

    if(email === "" || pass === ""){
        alert("Completa todos los campos");
        e.preventDefault();
    }
});