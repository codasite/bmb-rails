FROM node:18 AS builder
WORKDIR /usr/src/app

# Install dependencies
COPY package*.json .
RUN npm install
COPY . .
# Build the app for standalone and wordpress environments
RUN npm run build:standalone

# don't need dev dependencies after build
RUN npm prune --production

FROM node:18-slim
WORKDIR /usr/src/app

COPY --from=builder /usr/src/app/build/standalone /usr/src/app/build/standalone
COPY --from=builder /usr/src/app/node_modules /usr/src/app/node_modules

# In production, this container is mainly used for serving static files from a volume
CMD ["tail", "-f", "/dev/null"]