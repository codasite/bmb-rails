# Build stage
FROM ghcr.io/puppeteer/puppeteer:latest
USER root
WORKDIR /usr/src/app

# Install dependencies
COPY package*.json ./
COPY tsconfig.json ./
RUN npm install
# Copy your source code and perform the build
COPY . .

# Change ownership to pptruser for all application files
RUN chown -R pptruser:pptruser /usr/src/app

USER pptruser
RUN node /usr/src/app/node_modules/puppeteer/install.mjs

# Expose port and define the CMD as per your requirements
EXPOSE 3000
CMD ["npm", "run", "dev"]
