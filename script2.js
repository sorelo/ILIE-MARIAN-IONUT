const btnDetalii = document.getElementById("btnDetalii");
const divDetalii = document.getElementById("detalii");
const spanData = document.getElementById("dataProdus");

const luni = [
    "Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie",
    "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"
];

divDetalii.classList.add("ascuns");

const data = new Date();
const zi = data.getDate();
const luna = luni[data.getMonth()];
const an = data.getFullYear();

spanData.textContent = `${zi} ${luna} ${an}`;

btnDetalii.addEventListener("click", function () {
    divDetalii.classList.toggle("ascuns");

    if (divDetalii.classList.contains("ascuns")) {

        btnDetalii.textContent = "Afișează detalii";
    } else {

        btnDetalii.textContent = "Ascunde detalii";
    }
});