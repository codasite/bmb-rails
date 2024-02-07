import 'dotenv/config'

export const HEADLESS: boolean | "new" = process.env.HEADLESS === "false" ? false: "new";
export const HOST: string = process.env.HOST || "0.0.0.0";
export const PORT: number = parseInt(process.env.PORT || "3000");