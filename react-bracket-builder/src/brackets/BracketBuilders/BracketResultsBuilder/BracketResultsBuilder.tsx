import React, { useEffect, useState } from 'react'
import { logger } from '../../../utils/Logger'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context/context'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { ResultsBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import { bracketApi } from '../../shared/api/bracketApi'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
} from '../../shared/components/HigherOrder'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { useWindowDimensions } from '../../../utils/hooks'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { PaginatedResultsBuilder } from './PaginatedResultsBuilder/PaginatedResultsBuilder'
import { Checkbox } from './Checkbox'
import { BracketResultsBuilderContext } from './context'
import { getDashboardPath } from '../../shared'

interface BracketResultsBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  bracket?: any
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
}

const BracketResultsBuilder = (props: BracketResultsBuilderProps) => {
  const { matchTree, setMatchTree, bracket, setBracketMeta, darkMode } = props

  const [notifyParticipants, setNotifyParticipants] = useState(true)
  const [bracketId, setBracketId] = useState(0)

  const { width: windowWidth, height: windowHeight } = useWindowDimensions()
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  const toggleNotifyParticipants = () => {
    setNotifyParticipants(!notifyParticipants)
  }

  useEffect(() => {
    if (bracket) {
      const numTeams = bracket.numTeams
      const matches = bracket.matches
      const results = bracket.results
      const meta = getBracketMeta(bracket)
      setBracketMeta?.(meta)
      setBracketId(bracket.id)
      let tree: MatchTree | null
      if (results && results.length > 0) {
        tree = MatchTree.fromPicks(numTeams, matches, results)
      } else {
        tree = MatchTree.fromMatchRes(numTeams, matches)
      }
      if (tree) {
        setMatchTree?.(tree)
      } else {
        console.error('no tree')
      }
    } else {
      console.error('no bracket')
    }
  }, [])

  const complete = matchTree && matchTree.allPicked()

  const handleUpdatePicks = () => {
    if (matchTree) {
      const picks = matchTree.toMatchPicks()
      if (!picks || picks.length === 0) return
      const complete = matchTree.allPicked()
      const data = {
        results: picks,
        shouldNotifyResultsUpdated: notifyParticipants,
      }
      bracketApi
        .updateBracket(bracketId, data)
        .then(() => {
          const dashboardUrl = getDashboardPath('hosting', 'closed')
          if (dashboardUrl) window.location.href = dashboardUrl
        })
        .catch((err) => {
          console.error(err)
          logger.error(err)
        })
    }
  }

  if (showPaginated) {
    return (
      <BracketResultsBuilderContext.Provider
        value={{ notifyParticipants, toggleNotifyParticipants }}
      >
        <PaginatedResultsBuilder
          {...props}
          handleUpdatePicks={handleUpdatePicks}
        />
      </BracketResultsBuilderContext.Provider>
    )
  }
  return (
    <div
      className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover ${
        darkMode ? ' tw-dark' : ''
      }`}
      style={{
        backgroundImage: `url(${darkMode ? darkBracketBg : lightBracketBg})`,
      }}
    >
      {matchTree && (
        <div
          className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-[57px]`}
        >
          <div className="tw-py-[116px]">
            <ResultsBracket matchTree={matchTree} setMatchTree={setMatchTree} />
          </div>
          <div
            className={`tw-flex tw-flex-col tw-gap-24 ${
              !complete ? ' tw-max-w-[470px] tw-w-full' : ''
            }`}
          >
            <ActionButton
              variant="green"
              size="big"
              onClick={handleUpdatePicks}
            >
              {complete ? 'Complete Bracket' : 'Update Picks'}
            </ActionButton>
            <div className="tw-flex tw-gap-20 tw-items-center tw-self-center">
              <Checkbox
                id="notify-participants-check"
                checked={notifyParticipants}
                onChange={() => setNotifyParticipants(!notifyParticipants)}
              />
              <label
                htmlFor="notify-participants-check"
                className="tw-font-500 tw-text-24"
              >
                Notify Participants
              </label>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

const Wrapped = WithDarkMode(
  WithMatchTree(WithBracketMeta(BracketResultsBuilder))
)

export default Wrapped
