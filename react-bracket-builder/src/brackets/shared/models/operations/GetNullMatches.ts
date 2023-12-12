export const getNullMatches = (numRounds: number): null[][] => {
  let rounds: any[] = []
  for (let i = numRounds - 1; i >= 0; i--) {
    rounds.push(new Array(Math.pow(2, i)).fill(null))
  }
  return rounds
}
