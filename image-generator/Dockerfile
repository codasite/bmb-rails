# Build stage
FROM ghcr.io/puppeteer/puppeteer:23.9.0 AS builder
USER root
WORKDIR /usr/src/app

# Install dependencies
COPY package*.json ./
COPY tsconfig.json ./
RUN npm install
# Copy your source code and perform the build
COPY . .
RUN npm run build

RUN npm prune --production

# Change ownership to pptruser for all application files
RUN chown -R pptruser:pptruser /usr/src/app

# Runtime stage
FROM ghcr.io/puppeteer/puppeteer:23.9.0
USER pptruser
WORKDIR /usr/src/app

# Copy built application from the builder stage
COPY --from=builder /usr/src/app/dist /usr/src/app/dist
COPY --from=builder /usr/src/app/node_modules /usr/src/app/node_modules
COPY --from=builder /usr/src/app/package.json /usr/src/app/package.json

RUN node /usr/src/app/node_modules/puppeteer/install.mjs

# Expose port and define the CMD as per your requirements
EXPOSE 3000
CMD ["node", "dist/index.js"]
