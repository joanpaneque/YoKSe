const { Client, Events } = require('discord.js');
require('dotenv').config();


const client = new Client({
    intents: 3276799
});

client.on(Events.ClientReady, async () => { 
    console.log(`Conectado como ${client.user.username}`);
    // escribir un mensaje en un canal especifico
    const channel = await client.channels.fetch('1270885899651121152');
    channel.send(`Vuelvo a estar en linea (${new Date().toLocaleString()})`);
});

client.on(Events.MessageCreate, async (message) => {
    if (message.author.bot) return;
    // comprobar si el mensaje es un link. Si no es un link, eliminar el mensaje y enviar un mensaje de advertencia.
    // si es un link, comprobar si el link es de instagram o tiktok y escribir de que red social es.
    if (!message.content.startsWith('https://')) {
        message.channel.send('No puedes enviar mensajes que no sean links');
    } else {
        if (message.content.includes('instagram.com')) {
            // hacer un post a 
        } else if (message.content.includes('tiktok.com')) {
            message.channel.send('Este link es de TikTok');
        }
    }
});

client.login(process.env.DISCORD_BOT_TOKEN);