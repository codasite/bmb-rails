import React, { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMetaContext, DarkModeContext } from '../../shared/context'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { ResultsBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import checkIcon from '../../shared/assets/check.svg'
import { bracketApi } from '../../shared/api/bracketApi'

const CustomCheckbox = (props: any) => {
  const { id, checked, onChange } = props

  const baseStyles = [
    'tw-appearance-none',
    'tw-h-32',
    'tw-w-32',
    'tw-rounded-8',
    'tw-cursor-pointer',
  ]

  const uncheckedStyles = ['tw-border', 'tw-border-solid', 'tw-border-white']

  const checkedStyles = ['tw-bg-white', 'tw-bg-no-repeat', 'tw-bg-center']

  const styles = baseStyles
    .concat(checked ? checkedStyles : uncheckedStyles)
    .join(' ')

  return (
    <input
      type="checkbox"
      id={id}
      className={styles}
      checked={checked}
      onChange={onChange}
      style={{ backgroundImage: checked ? `url(${checkIcon})` : 'none' }}
    />
  )
}

interface TournamentResultsBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  tournament?: any
  myTournamentsUrl?: string
}

const TournamentResultsBuilder = (props: TournamentResultsBuilderProps) => {
  const { matchTree, setMatchTree, tournament, myTournamentsUrl } = props

  const [bracketTitle, setBracketTitle] = useState('')
  const [bracketDate, setBracketDate] = useState('')
  const [notifyParticipants, setNotifyParticipants] = useState(true)
  const [tournamentId, setTournamentId] = useState(0)

  useEffect(() => {
    if (tournament && tournament.bracketTemplate) {
      const template = tournament.bracketTemplate
      const numTeams = template.numTeams
      const matches = template.matches
      const results = tournament.results
      setBracketTitle(tournament.title)
      setBracketDate(tournament.date)
      setTournamentId(tournament.id)
      let tree: MatchTree | null
      if (results && results.length > 0) {
        tree = MatchTree.fromPicks(numTeams, matches, results)
      } else {
        tree = MatchTree.fromMatchRes(numTeams, matches)
      }
      if (tree) {
        setMatchTree?.(tree)
      }
    }
  }, [])

  const darkMode = true
  const complete = matchTree && matchTree.allPicked()

  const handleUpdatePicks = () => {
    if (matchTree) {
      const picks = matchTree.toMatchPicks()
      if (!picks || picks.length === 0) return
      const complete = matchTree.allPicked()
      const data = {
        results: picks,
        status: complete ? 'complete' : undefined,
        updateNotifyParticipants: notifyParticipants,
      }
      bracketApi
        .updateTournament(tournamentId, data)
        .then((res) => {
          console.log(res)
        })
        .catch((err) => {
          console.log(err)
        })
        .finally(() => {
          if (myTournamentsUrl) window.location.href = myTournamentsUrl || ''
        })
    }
  }

  return (
    <BracketMetaContext.Provider
      value={{ title: bracketTitle, date: bracketDate }}
    >
      <DarkModeContext.Provider value={darkMode}>
        <div
          className={`wpbb-reset tw-uppercase tw-bg-no-repeat tw-bg-top tw-bg-cover${
            darkMode ? ' tw-dark' : ''
          }`}
          style={{
            backgroundImage: `url(${
              darkMode ? darkBracketBg : lightBracketBg
            })`,
          }}
        >
          {matchTree && (
            <div
              className={`tw-flex tw-flex-col tw-items-center tw-max-w-screen-lg tw-m-auto tw-pb-[57px]`}
            >
              <div className="tw-py-[116px]">
                <ResultsBracket
                  matchTree={matchTree}
                  setMatchTree={setMatchTree}
                />
              </div>
              <div
                className={`tw-flex tw-flex-col tw-gap-24${
                  !complete ? ' tw-max-w-[470px] tw-w-full' : ''
                }`}
              >
                <ActionButton variant="big-yellow" onClick={handleUpdatePicks}>
                  {complete ? 'Complete Tournament' : 'Update Picks'}
                </ActionButton>
                <div className="tw-flex tw-gap-20 tw-items-center tw-self-center">
                  <CustomCheckbox
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
      </DarkModeContext.Provider>
    </BracketMetaContext.Provider>
  )
}

export default TournamentResultsBuilder
