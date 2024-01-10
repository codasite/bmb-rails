export interface PaymentIntentReq {
  playId: number
}

export interface PaymentIntentRes {
  amount: number
  clientSecret: string
}
