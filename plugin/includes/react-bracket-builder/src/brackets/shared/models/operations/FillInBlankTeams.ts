import { Round } from '../Round'
import { Team } from '../Team'

export const fillInBlankTeams = (rounds: Round[]) => {
  rounds.forEach((round) => {
    round.matches.forEach((match) => {
      if (!match) {
        return
      }
      if (!match.left && !match.getTeam1()) {
        match.setTeam1(new Team())
      }
      if (!match.right && !match.getTeam2()) {
        match.setTeam2(new Team())
      }
    })
  })
}
