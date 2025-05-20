// Importa los temas CSS (esto va en app.js, ya lo hiciste bien)

document.addEventListener("DOMContentLoaded", () => {
  const selector = document.getElementById("selectorTemas");

  // Crear o buscar el <style> donde inyectar el tema
  let styleTag = document.getElementById("dynamicThemeStyle");
  if (!styleTag) {
    styleTag = document.createElement("style");
    styleTag.id = "dynamicThemeStyle";
    document.head.appendChild(styleTag);
  }

  // Función para aplicar un tema
  function aplicarTema(nombreTema) {
    if (temas[nombreTema]) {
      styleTag.textContent = temas[nombreTema];
      localStorage.setItem('temaSeleccionado', nombreTema); // Guardar tema
    } else {
      styleTag.textContent = temas['clasico'];
      localStorage.setItem('temaSeleccionado', 'clasico');
    }
  }

  // Cargar tema guardado o por defecto
  const temaGuardado = localStorage.getItem('temaSeleccionado') || 'clasico';
  aplicarTema(temaGuardado);

  // Actualizar el selector visualmente si existe
  if (selector) {
    selector.value = temaGuardado;

    selector.addEventListener("change", function () {
      aplicarTema(this.value);
    });
  }
});
