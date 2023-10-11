import React, { useEffect, useState } from 'react'
import { bracketApi } from '../../shared/api/bracketApi'
import { MatchTree, WildcardPlacement } from '../../shared/models/MatchTree'
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage, NumTeamsPickerState } from './NumTeamsPage'
import {
  TemplateReq,
  TemplateRes,
  TournamentReq,
} from '../../shared/api/types/bracket'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
import { BracketMeta } from '../../shared/context'

const defaultBracketName = 'MY BRACKET NAME'
const defaultInitialPickerIndex = 0
const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [2, 17, 33]
const teamPickerMax = [31, 63, 64]
interface TemplateBuilderProps {
  template?: TemplateRes
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
  console.log('template builder')
  const [currentPage, setCurrentPage] = useState('num-teams')
  const [numTeams, setNumTeams] = useState(
    teamPickerDefaults[defaultInitialPickerIndex]
  )
  const [wildcardPlacement, setWildcardPlacement] = useState(
    WildcardPlacement.Split
  )
  const [teamPickerState, setTeamPickerState] = useState<NumTeamsPickerState[]>(
    teamPickerDefaults.map((val, i) => ({
      currentValue: val,
      selected: i === defaultInitialPickerIndex,
    }))
  )
  useEffect(() => {
    setBracketMeta?.({
      title: bracketMeta?.title || defaultBracketName,
      date: bracketMeta?.date || new Date().getFullYear().toString(),
    })
  }, [])

  useEffect(() => {
    if (template) {
      const { title, numTeams, wildcardPlacement, matches } = template
      console.log('template found', template)
      setBracketMeta?.({
        title: title || defaultBracketName,
        date: template.date,
      })
      setNumTeams(numTeams)
      setWildcardPlacement(wildcardPlacement)
      pickerStateFromNumTeams(numTeams)
      if (matches && matches.length > 0) {
        console.log('matches found', matches)
        const newMatches = matches.map((match) => ({
          roundIndex: match.roundIndex,
          matchIndex: match.matchIndex,
          team1: { name: match.team1?.name },
          team2: { name: match.team2?.name },
        }))
        setMatchTree?.(
          MatchTree.fromMatchRes(numTeams, newMatches, wildcardPlacement)
        )
        setCurrentPage('add-teams')
      }
    }
  }, [template])
  const pickerStateFromNumTeams = (numTeams: number) => {
    const initialPickerIndex = teamPickerMax.findIndex((max) => numTeams <= max)
    if (initialPickerIndex >= 0) {
      const picker = teamPickerState[initialPickerIndex]
      const newPicker = {
        ...picker,
        currentValue: numTeams,
        selected: true,
      }
      const newPickers = teamPickerState.map((picker, i) => {
        if (i === initialPickerIndex) {
          return newPicker
        }
        return {
          ...picker,
          selected: false,
        }
      })
      setTeamPickerState(newPickers)
    }
  }
  const handleAddTeamsClick = () => {
    setCurrentPage('add-teams')
  }
  const getTemplateReq = () => {
    const req: TemplateReq = {
      title: bracketMeta.title,
      date: bracketMeta.date,
      numTeams: numTeams,
      wildcardPlacement: wildcardPlacement,
      matches: matchTree.toMatchReq(),
      status: 'publish',
    }
    return req
  }
  const handleSaveTemplateClick = () => {
    if (!matchTree || !matchTree.allTeamsAdded()) {
      return
    }
    bracketApi
      .createTemplate(getTemplateReq())
      .then((res) => {
        if (saveTemplateLink) {
          window.location.href = saveTemplateLink
        }
      })
      .catch((err) => {
        console.error(err)
      })
  }
  const handleSaveTournamentClick = (tournamentName: string) => {
    const tournamentReq: TournamentReq = {
      title: tournamentName,
      status: 'publish',
      bracketTemplate: getTemplateReq(),
    }
    return bracketApi.createTournament(tournamentReq).then((res) => {
      if (saveTournamentLink) {
        window.location.href = saveTournamentLink
      }
    })
  }
  return (
    <div className="wpbb-reset tw-uppercase">
      {currentPage === 'num-teams' && (
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
      )}
      {currentPage === 'add-teams' && (
        <AddTeamsPage
          matchTree={matchTree}
          setMatchTree={setMatchTree}
          handleBack={() => setCurrentPage('num-teams')}
          handleSaveTemplate={handleSaveTemplateClick}
          handleCreateTournament={handleSaveTournamentClick}
        />
      )}
    </div>
  )
}
const WrappedTemplateBuilder = WithProvider(
  WithDarkMode(WithMatchTree(WithBracketMeta(TemplateBuilder)))
)
export default WrappedTemplateBuilder
