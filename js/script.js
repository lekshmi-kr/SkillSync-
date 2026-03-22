function searchTutor() {

let input = document.getElementById("searchBar").value.toLowerCase();
let cards = document.getElementsByClassName("tutor-card");
let visible = 0;

for (let i = 0; i < cards.length; i++) {

let skill = cards[i].getElementsByClassName("skill")[0];
let text = skill.textContent.toLowerCase();

if (text.includes(input)) {
cards[i].style.display = "block";
visible++;
}
else {
cards[i].style.display = "none";
}

}

if(visible === 0){
document.getElementById("noResult").style.display = "block";
}else{
document.getElementById("noResult").style.display = "none";
}

}

function openProfile(name, skill, exp, location, rating, image){

localStorage.setItem("name", name);
localStorage.setItem("skill", skill);
localStorage.setItem("exp", exp);
localStorage.setItem("location", location);
localStorage.setItem("rating", rating);
localStorage.setItem("image", image);

window.location.href = "profile.html";

}

document.querySelector(".signup-form")?.addEventListener("submit", function(e){

let password = document.querySelectorAll("input[type='password']")[0]?.value;
let confirm = document.querySelectorAll("input[type='password']")[1]?.value;

if(confirm && password !== confirm){
alert("Passwords do not match!");
e.preventDefault();
}

});

function openContact(){
document.getElementById("contactPopup").style.display = "flex";
}

function closeContact(){
document.getElementById("contactPopup").style.display = "none";
}

function sendMessage(){
alert("Message sent successfully!");
closeContact();
}

function matchTutor(){

let input = document.getElementById("userSkill").value.toLowerCase();
let cards = document.getElementsByClassName("tutor-card");

for(let i=0; i<cards.length; i++){

let skill = cards[i].getElementsByClassName("skill")[0].innerText.toLowerCase();

if(skill.includes(input)){
cards[i].style.display = "block";
}
else{
cards[i].style.display = "none";
}

}

}