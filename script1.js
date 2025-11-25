const inputActivitate = document.getElementById("inputActivitate");
const btnAdauga = document.getElementById("btnAdauga");
const listaActivitati = document.getElementById("listaActivitati");

const luni = [
    "Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie",
    "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"
];

btnAdauga.addEventListener("click", function() {

    const textActivitate = inputActivitate.value;

    if (textActivitate !== "") {

        const elementNou = document.createElement("li");

        const dataCurenta = new Date();
        const zi = dataCurenta.getDate();
        const luna = luni[dataCurenta.getMonth()];
        const an = dataCurenta.getFullYear();

        elementNou.textContent = `${textActivitate} - adăugată la: ${zi} ${luna} ${an}`;

        listaActivitati.appendChild(elementNou);

        inputActivitate.value = "";
    } else {
        alert("Te rog introdu o activitate!");
    }
});