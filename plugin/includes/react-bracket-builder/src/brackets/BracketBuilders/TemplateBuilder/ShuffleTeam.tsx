export class ShuffleTeam {
  static getTeamNames = (rounds, size, wildCard) => {
    var randomTeams: String[] = []
    let firstRoundData = rounds[size].matches
    if (wildCard !== 0) {
      const secondRoundData = rounds[size - 1].matches
      firstRoundData = firstRoundData.concat(secondRoundData)
    }
    firstRoundData.forEach((key) => {
      if (key != null) {
        if (key.team1?.name) {
          randomTeams.push(key.team1.name)
        }
        if (key.team2?.name) {
          randomTeams.push(key.team2.name)
        }
      }
    })
    return ShuffleTeam.shuffle(randomTeams)
  }

  static shuffle = (teams) => {
    let currentIndex = teams.length,
      randomIndex

    while (currentIndex != 0) {
      randomIndex = Math.floor(Math.random() * currentIndex)
      currentIndex--

      ;[teams[currentIndex], teams[randomIndex]] = [
        teams[randomIndex],
        teams[currentIndex],
      ]
    }
    return teams
  }

  static updateMatchTree = (shuffledTeamNames, roundsData, size, wildCard) => {
    let value = 0
    const updateMatches = (matches) => {
      for (const match of matches) {
        const team1 = match?.team1
        const team2 = match?.team2
        if (team1) {
          team1.name = shuffledTeamNames[value]
          value++
        }
        if (team2) {
          team2.name = shuffledTeamNames[value]
          value++
        }
      }
    }

    updateMatches(roundsData[size].matches)

    if (wildCard !== 0) {
      updateMatches(roundsData[size - 1].matches)
    }

    return roundsData
  }
}
