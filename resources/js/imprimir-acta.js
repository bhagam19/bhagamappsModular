window.imprimir = function () {
    const contenido = document.querySelector('.contenido-principal');

    if (!contenido) {
        alert("No se encontró el contenido del acta.");
        return;
    }

    const ventana = window.open('', '_blank', 'width=1000,height=800');

    ventana.document.open();
    ventana.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Imprimir Acta</title>

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        
            <style>

                @page {                    
                    @top-center {
                        content: "Página " counter(page) " de " counter(pages);
                        font-size: 10pt;
                    }
                }
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Helvetica, Times, serif;
                }
                .contenedor-acta {
                    margin: 2cm;
                    padding: 1cm 2cm;
                    font-size: 11pt;
                    max-width: 900px;
                    min-height: 1200px;
                }
                .linea-verde {
                    background-color: #013801;
                    height: 0.8mm;
                    border: none;
                    margin: 0;
                }
                .linea-blanca {
                    background-color: white;
                    height: 0.8mm;
                    border: none;
                    margin: 0;
                }
                .linea-azul {
                    background-color: #01018a;
                    height: 0.8mm;
                    border: none;
                    margin: 0;
                }
                @media print {   

                    body {
                        margin: 0;
                        padding: 0;
                        font-family: Helvetica, Times, serif;
                    }

                    .contenido-principal {
                        margin: 160px 0 120px 0;    
                        padding: 0;                    
                        font-size: 11pt;
                    }

                    .linea-verde {
                        background-color: #013801;
                        height: 0.8mm;
                        border: none;
                        margin: 0;
                    }
                    .linea-blanca {
                        background-color: white;
                        height: 0.8mm;
                        border: none;
                        margin: 0;
                    }
                    .linea-azul {
                        background-color: #01018a;
                        height: 0.8mm;
                        border: none;
                        margin: 0;
                    }
                          
                    header, footer {
                        position: fixed;
                        left: 0;
                        right: 0;
                        background: white;
                        z-index: 10;
                    }
                    header {
                        top: 0;
                        height: 140px;
                    }
                    footer {
                        bottom: 0;
                        height: 90px;
                        text-align: center;
                    }
                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                }
            </style>
        </head>
        <body>
            ${contenido.outerHTML}            
        </body>
        </html>
    `);
    ventana.document.close();

    ventana.onload = function() {
        ventana.focus();
        ventana.print();
        ventana.onafterprint = function() {
            ventana.close();
        };
    };
};
