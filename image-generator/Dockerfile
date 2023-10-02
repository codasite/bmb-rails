FROM ghcr.io/puppeteer/puppeteer:latest
# Create app directory
USER root
WORKDIR /usr/src/app
# Install app dependencies
# RUN chown -R node:node /usr/src/app
COPY package*.json ./
RUN npm install
# If you are building your code for production
# RUN npm ci --only=production
# Bundle app source
COPY . .
# Build app
EXPOSE 3000
RUN chown -R pptruser:pptruser /usr/src/app
USER pptruser
CMD ["npm", "start"]
