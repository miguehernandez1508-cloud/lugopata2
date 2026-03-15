<script>
const canvas = document.getElementById("canvasFirma");
const ctx = canvas.getContext("2d");


ctx.strokeStyle = "black";
ctx.lineWidth = 2;
ctx.lineCap = "round";

let dibujando = false;

canvas.addEventListener("mousedown", e => {
    dibujando = true;
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
});

canvas.addEventListener("mousemove", e => {
    if(dibujando){
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
    }
});

canvas.addEventListener("mouseup", e => { dibujando = false; });
canvas.addEventListener("mouseout", e => { dibujando = false; });


function limpiarFirma(){
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}


document.querySelector("form").addEventListener("submit", function(e){
    const dataURL = canvas.toDataURL("image/png");
    document.getElementById("firma").value = dataURL;
});
</script>
