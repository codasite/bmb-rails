import React, { useState } from 'react'
import { bracketApi } from '../shared/api/bracketApi'
import { MatchTree, WildcardPlacement } from '../shared/models/MatchTree'
//@ts-ignore
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage, NumTeamsPickerState } from './NumTeamsPage'
import { TemplateReq } from '../shared/api/types/bracket'

const defaultBracketName = "MY BRACKET NAME"

const initialPickerIndex = 0
const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [1, 17, 33]
const teamPickerMax = [31, 63, 64]

interface TemplateBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  saveTemplateLink?: string
  saveTournamentLink?: string
}

const TemplateBuilder = (props: TemplateBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    saveTemplateLink,
    saveTournamentLink
  } = props

  const [currentPage, setCurrentPage] = useState('num-teams')
  const [bracketTitle, setBracketTitle] = useState(defaultBracketName)
  const [numTeams, setNumTeams] = useState(teamPickerDefaults[initialPickerIndex])
  const [wildcardPlacement, setWildcardPlacement] = useState(WildcardPlacement.Top)
  const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
    teamPickerDefaults.map((val, i) => ({
      currentValue: val,
      selected: i === initialPickerIndex
    }))
  )

  const handleAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }

  const handleSaveTemplateClick = () => {
    if (!matchTree || !matchTree.allTeamsAdded()) {
      return
    }

    const req: TemplateReq = {
      title: bracketTitle,
      numTeams: numTeams,
      wildcardPlacement: wildcardPlacement,
      matches: matchTree.toMatchReq()
    }
    console.log(req)

    // bracketApi.createTemplate()

  }

  const handleSaveTournamentClick = () => {
    console.log('save tournament')
  }

  return (
    <div className='wpbb-reset tw-uppercase'>
      {currentPage === 'num-teams' &&
        <NumTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          onAddTeamsClick={handleAddTeamsClick}
          bracketTitle={bracketTitle}
          setBracketTitle={setBracketTitle}
          numTeams={numTeams}
          setNumTeams={setNumTeams}
          teamPickerDefaults={teamPickerDefaults}
          teamPickerMin={teamPickerMin}
          teamPickerMax={teamPickerMax}
          wildcardPlacement={wildcardPlacement}
          setWildcardPlacement={setWildcardPlacement}
          teamPickerState={teamPickerState}
          setTeamPickerState={setTeamPickerState}
        />
      }
      {currentPage === 'add-teams' &&
        <AddTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          bracketTitle={bracketTitle}
          handleBack={() => setCurrentPage('num-teams')}
          handleSaveTemplate={handleSaveTemplateClick}
          handleSaveTournament={handleSaveTournamentClick}
        />
      }
    </div>
  )
}

export default TemplateBuilder