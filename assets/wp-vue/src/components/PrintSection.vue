<script setup>
import { ref } from "vue";

const printRef = ref(null);

const printHandler = () => {
  const original = printRef.value;
  const clone = original.cloneNode(true);

  // Copy input/textarea values
  const originalInputs = original.querySelectorAll("input, textarea");
  const clonedInputs = clone.querySelectorAll("input, textarea");

  originalInputs.forEach((input, i) => {
    const cloned = clonedInputs[i];
    if (!cloned) return;

    if (cloned.tagName === "TEXTAREA") {
      cloned.innerHTML = input.value;
    } else if (cloned.tagName === "INPUT") {
      cloned.setAttribute("value", input.value);
    }
  });

  // Open print window
  const printWindow = window.open("", "_blank", "width=900,height=650");

  if (!printWindow) {
    console.error("Popup blocked");
    return;
  }

  const head = document.head;
  const styleTags = Array.from(head.querySelectorAll("style"))
    .map((el) => el.outerHTML)
    .join("\n");
  const linkTags = Array.from(head.querySelectorAll('link[rel="stylesheet"]'))
    .map((el) => el.outerHTML)
    .join("\n");

  // Build print HTML
  const html = `
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8" />
                        <title>Print Invoice</title>
                        ${linkTags}
                        ${styleTags}
                        <style>
                            @media print {
                                body {
                                    background: white !important;
                                    color: black !important;
                                    padding: 1rem;
                                    font-family: inherit;
                                }
                                input, textarea {
                                    font-size: 1rem;
                                    color: black;
                                }
                            }
                            body {
                                margin: 0;
                                padding: 0;
                            }
                        </style>
                    </head>
                    <body>
                        <div id="print-content">
                            ${clone.outerHTML}
                        </div>
                        <script>
                            window.onload = function() {
                                window.print();
                                window.onafterprint = function () {
                                    window.close();
                                };
                            }
                        <\/script>
                    </body>
                    </html>
                `;

  // Write and print
  printWindow.document.open();
  printWindow.document.write(html);
  printWindow.document.close();
};

defineExpose({ printHandler });
</script>
<template>
  <div>
    <!-- print -->
    <div ref="printRef">
      <slot />
    </div>
  </div>
</template>
