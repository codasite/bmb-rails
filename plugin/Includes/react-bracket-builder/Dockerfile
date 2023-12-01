FROM node:18
# Create react app directory
WORKDIR /usr/src/app
# Install dependencies
COPY package*.json .
RUN npm install
# Bundle app source
COPY . .

CMD ["npm", "run", "build:standalone"]