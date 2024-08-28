import React, { useEffect, useState } from 'react'
import { bracketApi } from '../../shared/api/bracketApi'
import { MatchTree } from '../../shared/models/MatchTree'
import { AddTeamsPage } from './AddTeamsPage'
import { NumTeamsPage, NumTeamsPickerState } from './NumTeamsPage'
import { BracketReq, BracketRes } from '../../shared/api/types/bracket'
import {
  WithBracketMeta,
  WithMatchTree,
} from '../../shared/components/HigherOrder'
import { BracketMeta } from '../../shared/context/context'
import { WildcardPlacement } from '../../shared/models/WildcardPlacement'
import { getBracketMeta } from '../../shared/components/Bracket/utils'
import { getDashboardPath } from '../../shared'

const defaultInitialPickerIndex = 0
export const teamPickerDefaults = [16, 32, 64]
export const teamPickerMin = [4, 17, 33]
export const teamPickerMax = [31, 63, 64]
interface BracketBuilderProps {
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

const BracketBuilder = (props: BracketBuilderProps) => {
  const { matchTree, setMatchTree, bracket, bracketMeta, setBracketMeta } =
    props
  const [currentPage, setCurrentPage] = useState('num-teams')
  // const [currentPage, setCurrentPage] = useState('add-teams')
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
  const [month, setMonth] = useState('')
  const [year, setYear] = useState('')
  const [processing, setProcessing] = useState(false)

  useEffect(() => {
    setBracketMeta?.({
      ...bracketMeta,
      date: `${month} ${year}`,
    })
  }, [month, year])

  useEffect(() => {
    setBracketMeta?.({
      title: bracketMeta?.title || '',
      date: bracketMeta?.date || '',
    })
  }, [])

  useEffect(() => {
    if (bracket) {
      const { numTeams, wildcardPlacement, matches, month, year } = bracket
      setMonth(month)
      setYear(year)
      const { title, date } = getBracketMeta(bracket)
      setBracketMeta?.({
        title: `${title} Copy`,
        date: date,
      })
      setNumTeams(numTeams)
      setWildcardPlacement(wildcardPlacement)
      pickerStateFromNumTeams(numTeams)
      if (matches && matches.length > 0) {
        const newMatches = matches.map((match) => ({
          roundIndex: match.roundIndex,
          matchIndex: match.matchIndex,
          team1: { name: match.team1?.name },
          team2: { name: match.team2?.name },
        }))
        setMatchTree?.(
          MatchTree.fromMatchRes(
            { numTeams, matches: newMatches },
            wildcardPlacement
          )
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
      month: month,
      year: year,
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
    setProcessing(true)
    bracketApi
      .createBracket(getBracketReq())
      .then((res) => {
        const dashboardUrl = getDashboardPath('hosting', 'private')
        if (dashboardUrl) {
          window.location.href = dashboardUrl
        }
      })
      .catch((err) => {
        console.error(err)
      })
      .finally(() => {
        setProcessing(false)
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
          month={month}
          setMonth={setMonth}
          year={year}
          setYear={setYear}
          processing={processing}
        />
      )}
    </div>
  )
}
const WrappedBracketBuilder = WithMatchTree(WithBracketMeta(BracketBuilder))
export default WrappedBracketBuilder
