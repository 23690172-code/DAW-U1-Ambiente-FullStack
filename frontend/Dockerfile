FROM node:24-alpine

WORKDIR /app

# Copiar los archivos de dependencias
COPY package*.json ./

# Instalar las dependencias
RUN npm install --legacy-peer-deps

# Copiar el resto del código (se sobrescribirá con el volumen en desarrollo)
COPY . .

EXPOSE 5173

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]