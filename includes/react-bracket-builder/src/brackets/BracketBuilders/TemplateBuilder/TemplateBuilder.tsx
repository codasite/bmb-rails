import React, { useState, useEffect } from 'react'
import { bracketApi } from '../../shared/api/bracketApi'
import { MatchTree, WildcardPlacement } from '../../shared/models/MatchTree'
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage, NumTeamsPickerState } from './NumTeamsPage'
import { TemplateReq } from '../../shared/api/types/bracket'
import { WithBracketMeta, WithDarkMode, WithProvider, WithMatchTree } from '../../shared/components/HigherOrder'
import { BracketMeta } from '../../shared/context'

const defaultBracketName = "MY BRACKET NAME"

const initialPickerIndex = 0
const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [1, 17, 33]
const teamPickerMax = [31, 63, 64]

interface TemplateBuilderProps {
  template?: TemplateReq
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  saveTemplateLink?: string
  saveTournamentLink?: string
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

const TemplateBuilder = (props: TemplateBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    saveTemplateLink,
    saveTournamentLink,
    template,
    bracketMeta,
    setBracketMeta,

  } = props

  const [currentPage, setCurrentPage] = useState('num-teams')
  const [numTeams, setNumTeams] = useState(teamPickerDefaults[initialPickerIndex])
  const [wildcardPlacement, setWildcardPlacement] = useState(WildcardPlacement.Split)
  const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
    teamPickerDefaults.map((val, i) => ({
      currentValue: val,
      selected: i === initialPickerIndex
    }))
  )

  useEffect(() => {
    if (template) {
      setBracketMeta?.({
        title: template.title,
        date: '2021',
      })

      setNumTeams(template.numTeams)
      setWildcardPlacement(template.wildcardPlacement)
      setTeamPickerState(teamPickerState.map((picker, i) => ({
        ...picker,
        currentValue: template.numTeams,
        selected: i === initialPickerIndex
      })))
    }
  })

  const handleAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }

  const handleSaveTemplateClick = () => {
    if (!matchTree || !matchTree.allTeamsAdded()) {
      return
    }

    const req: TemplateReq = {
      title: bracketMeta.title,
      numTeams: numTeams,
      wildcardPlacement: wildcardPlacement,
      matches: matchTree.toMatchReq(),
      status: 'publish',
    }
    console.log(req)
    console.log(JSON.stringify(req))

    bracketApi.createTemplate(req)
      .then((res) => {
        console.log(res)
        if (saveTemplateLink) {
          window.location.href = saveTemplateLink
        }
      })
      .catch((err) => {
        console.log(err)
      })
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
          numTeams={numTeams}
          setNumTeams={setNumTeams}
          teamPickerDefaults={teamPickerDefaults}
          teamPickerMin={teamPickerMin}
          teamPickerMax={teamPickerMax}
          wildcardPlacement={wildcardPlacement}
          setWildcardPlacement={setWildcardPlacement}
          teamPickerState={teamPickerState}
          setTeamPickerState={setTeamPickerState}
          bracketMeta={bracketMeta}
          setBracketMeta={setBracketMeta}
        />
      }
      {currentPage === 'add-teams' &&
        <AddTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          handleBack={() => setCurrentPage('num-teams')}
          handleSaveTemplate={handleSaveTemplateClick}
          handleSaveTournament={handleSaveTournamentClick}
        />
      }
    </div>
  )
}

const WrappedTemplateBuilder = WithProvider(WithDarkMode(WithMatchTree(WithBracketMeta(TemplateBuilder))))
export default WrappedTemplateBuilder