FROM node:16-alpine

WORKDIR /app

# Copia arquivos de dependências e instala
COPY package*.json ./
RUN npm install

# Copia o restante do código
COPY . .

EXPOSE 3000

CMD ["npm", "start"]