FROM node:18 as builder
WORKDIR /usr/src/app

# Install app dependencies
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

ARG NODE_ENV=production
RUN if [ "$NODE_ENV" = "production" ]; \
    then npm prune --production; \
    fi

FROM node:18-slim
WORKDIR /usr/src/app

COPY --from=builder /usr/src/app/dist /usr/src/app/dist
COPY --from=builder /usr/src/app/node_modules /usr/src/app/node_modules
COPY --from=builder /usr/src/app/package.json /usr/src/app/package.json

# Build app
EXPOSE 3001
CMD ["node", "dist/index.js"]
