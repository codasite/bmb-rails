export const fillInEmptyMatches = (
  rounds: any[][],
  roundStart: number = 1
): any[][] => {
  const newRounds = rounds.map((round, roundIndex) => {
    if (roundIndex < roundStart) {
      return round
    }
    const newRound = round.map((match, matchIndex) => {
      if (match !== null) {
        return match
      }
      return { roundIndex: roundIndex, matchIndex: matchIndex }
    })
    return newRound
  })
  return newRounds
}
