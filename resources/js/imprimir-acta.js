window.imprimir = function () {
    const paginas = document.querySelectorAll('.contenido-principal');

    if (!paginas.length) {
        alert("No se encontró contenido para imprimir.");
        return;
    }

    let contenidoHTML = '';
    paginas.forEach(pagina => {
        contenidoHTML += pagina.outerHTML;
    });

    const ventana = window.open('', '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');

    ventana.document.open();
    ventana.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Imprimir Acta</title>
            <style>
                @media print {
                    body, div {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                }
            </style>
    </head>
        <body style="margin: 0; padding: 0;">
            ${contenidoHTML}
        </body>
        </html>
    `);
    ventana.document.close();

    ventana.onload = function () {
        ventana.focus();
        ventana.print();
        ventana.onafterprint = function () {
            ventana.close();
        };
    };
};
