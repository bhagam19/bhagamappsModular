<?php
if (function_exists('proc_open')) {
    echo "✅ proc_open está habilitada";
} else {
    echo "❌ proc_open sigue deshabilitada";
}
