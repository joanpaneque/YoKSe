const { Client, Events } = require('discord.js');
require('dotenv').config();
const axios = require('axios');


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
            // hacer una peticion http post a https://yokse.joanpaneque.com/api/video
            // con el link enviado por el usuario y type: 'instagram'
            message.channel.send('Este link es de Instagram');
            // get the channel name
            axios.post(`https://yokse.joanpaneque.com/api/video/${message.channel.name}`, {
            // axios.post(`http://localhost/api/videos/${message.channel.name}`, {
                url: message.content,
                type: 'instagram'
            }).then((response) => {
                if (response.data.error) {
                    message.channel.send("[ YoKSe ERROR ]: " + response.data.message);
                } else {
                    message.channel.send(response.data.message);
                }
            }).catch((error) => {
                message.channel.send(`Error inesperado`);
                console.log(error);
            });
            
        } else if (message.content.includes('tiktok.com')) {
            message.channel.send('Este link es de TikTok');
            axios.post(`https://yokse.joanpaneque.com/api/video/${message.channel.name}`, {
            // axios.post(`http://localhost/api/videos/${message.channel.name}`, {
                url: message.content,
                type: 'tiktok'
            }).then((response) => {
                if (response.data.error) {
                    message.channel.send("[ YoKSe ERROR ]: " + response.data.message);
                } else {
                    message.channel.send(response.data.message);
                }
            }).catch((error) => {
                message.channel.send(`Error inesperado`);
                console.log(error);
            });
        }
    }
});

client.login(process.env.DISCORD_BOT_TOKEN);