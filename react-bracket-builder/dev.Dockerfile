FROM node:18
WORKDIR /usr/src/app

# Install dependencies
COPY package*.json .
RUN npm install
COPY . .

CMD ["npm", "run", "dev:standalone"]