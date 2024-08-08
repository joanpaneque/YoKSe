if screen -list | grep -q "yokse_bot"; then
    echo "La sesión de screen 'yokse_bot' existe. Eliminándola..."
    screen -S yokse_bot -X quit
else
    echo "La sesión de screen 'yokse_bot' no existe."
fi
cd discord
screen -dmS yokse_bot node index.js