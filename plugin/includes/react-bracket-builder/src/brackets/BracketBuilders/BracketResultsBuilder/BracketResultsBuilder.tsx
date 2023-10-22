import React, { useEffect, useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMetaContext, DarkModeContext } from '../../shared/context'
import darkBracketBg from '../../shared/assets/bracket-bg-dark.png'
import lightBracketBg from '../../shared/assets/bracket-bg-light.png'
import { ResultsBracket } from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import checkIcon from '../../shared/assets/check.svg'
import { bracketApi } from '../../shared/api/bracketApi'
import {
  WithBracketMeta,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'

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

interface BracketResultsBuilderProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  bracket?: any
  myBracketsUrl?: string
}

const BracketResultsBuilder = (props: BracketResultsBuilderProps) => {
  const { matchTree, setMatchTree, bracket, myBracketsUrl } = props

  const [bracketTitle, setBracketTitle] = useState('')
  const [bracketMonth, setBracketMonth] = useState('')
  const [bracketYear, setBracketYear] = useState('')
  const [notifyParticipants, setNotifyParticipants] = useState(true)
  const [bracketId, setBracketId] = useState(0)
  console.log('bracket', bracket)

  useEffect(() => {
    if (bracket) {
      const numTeams = bracket.numTeams
      const matches = bracket.matches
      const results = bracket.results
      setBracketTitle(bracket.title)
      setBracketMonth(bracket.month)
      setBracketYear(bracket.year)
      setBracketId(bracket.id)
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
        updateNotifyParticipants: notifyParticipants,
      }
      bracketApi
        .updateBracket(bracketId, data)
        .then((res) => {
          console.log(res)
        })
        .catch((err) => {
          console.log(err)
        })
        .finally(() => {
          if (myBracketsUrl) window.location.href = myBracketsUrl || ''
        })
    }
  }

  return (
    <BracketMetaContext.Provider
      value={{ title: bracketTitle, month: bracketMonth, year: bracketYear }}
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
                  {complete ? 'Complete Bracket' : 'Update Picks'}
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

const Wrapped = WithProvider(
  WithMatchTree(WithBracketMeta(BracketResultsBuilder))
)

export default Wrapped
