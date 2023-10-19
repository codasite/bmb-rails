import React, { useEffect, useState } from 'react'
import { bracketApi } from '../../shared/api/bracketApi'
import { MatchTree } from '../../shared/models/MatchTree'
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage, NumTeamsPickerState } from './NumTeamsPage'
import { BracketReq, BracketRes } from '../../shared/api/types/bracket'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
import { BracketMeta } from '../../shared/context'
import { WildcardPlacement } from '../../shared/models/WildcardPlacement'

const defaultBracketName = 'MY BRACKET NAME'
const defaultInitialPickerIndex = 0
const teamPickerDefaults = [16, 32, 64]
const teamPickerMin = [4, 17, 33]
const teamPickerMax = [31, 63, 64]
interface BracketBuilderProps {
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  saveBracketLink?: string
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

const BracketBuilder = (props: BracketBuilderProps) => {
  const {
    matchTree,
    setMatchTree,
    saveBracketLink,
    bracket,
    bracketMeta,
    setBracketMeta,
  } = props
  console.log('bracket builder')
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
    if (bracket) {
      const { numTeams, wildcardPlacement, matches } = bracket
      console.log('bracket found', bracket)
      setBracketMeta?.({
        title: `${bracket.title} Copy` || defaultBracketName,
        date: bracket.date,
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
  }, [bracket])
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
  const getBracketReq = () => {
    const req: BracketReq = {
      title: bracketMeta.title,
      date: bracketMeta.date,
      numTeams: numTeams,
      wildcardPlacement: wildcardPlacement,
      matches: matchTree.toMatchReq(),
      status: 'private',
    }
    return req
  }
  const handleSaveBracketClick = () => {
    if (!matchTree || !matchTree.allTeamsAdded()) {
      return
    }
    bracketApi
      .createBracket(getBracketReq())
      .then((res) => {
        if (saveBracketLink) {
          window.location.href = saveBracketLink
        }
      })
      .catch((err) => {
        console.error(err)
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
          handleSaveBracket={handleSaveBracketClick}
        />
      )}
    </div>
  )
}
const WrappedBracketBuilder = WithProvider(
  WithDarkMode(WithMatchTree(WithBracketMeta(BracketBuilder)))
)
export default WrappedBracketBuilder
