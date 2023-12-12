export const getNumRounds = (numTeams: number): number => {
  return Math.ceil(Math.log2(numTeams))
}
