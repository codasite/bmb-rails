import React, { useState } from 'react'
import { MatchTree } from '../../shared/models/MatchTree'
import { ReactComponent as ArrowNarrowLeft } from '../../shared/assets/arrow-narrow-left.svg'
import iconBackground from '../../shared/assets/bmb_icon_white_02.png'
import {
  AddTeamsBracket,
  PaginatedDefaultBracket,
} from '../../shared/components/Bracket'
import { ActionButton } from '../../shared/components/ActionButtons'
import { ReactComponent as SaveIcon } from '../../shared/assets/save.svg'
import { ReactComponent as PlayIcon } from '../../shared/assets/play.svg'
import { useWindowDimensions } from '../../../utils/hooks'
import { TextFieldModal } from '../../../modals/TextFieldModal'

interface AddTeamsPageProps {
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  handleSaveTemplate: () => void
  handleCreateTournament: (tournamentName: string) => Promise<void>
  handleBack: () => void
}
export const AddTeamsPage = (props: AddTeamsPageProps) => {
  const {
    matchTree,
    setMatchTree,
    handleSaveTemplate,
    handleCreateTournament,
    handleBack,
  } = props
  const createDisabled = !matchTree || !matchTree.allTeamsAdded()
  const { width: windowWidth } = useWindowDimensions()
  const showPaginated = windowWidth < 768
  if (showPaginated) {
    return (
      <div className="tw-bg-dd-blue">
        <PaginatedDefaultBracket
          matchTree={matchTree}
          setMatchTree={setMatchTree}
        />
      </div>
    )
  }
  const [showModal, setShowModal] = useState(false)
  const [input, setInput] = useState('')
  const [hasError, setHasError] = useState(false)
  const [loading, setLoading] = useState(false)
  const handleSubmit = () => {
    if (!input) {
      setHasError(true)
      return
    }
    setLoading(true)
    handleCreateTournament(input).catch((err) => {
      console.error(err)
      setLoading(false)
      setHasError(true)
    })
  }
  return (
    <div
      className="tw-flex tw-flex-col tw-gap-60 tw-pt-30 tw-pb-60 tw-bg-no-repeat tw-bg-top tw-bg-cover"
      style={{ background: `url(${iconBackground}), #000225` }}
    >
      <div className="tw-px-60">
        <div className="tw-flex tw-p-16">
          <a
            href="#"
            className="tw-flex tw-gap-10 tw-items-center"
            onClick={handleBack}
          >
            <ArrowNarrowLeft />
            <span className="tw-font-500 tw-text-20 tw-text-white ">
              Create Template
            </span>
          </a>
        </div>
      </div>
      <div
        className={`tw-flex tw-flex-col tw-justify-center tw-items-center tw-max-w-screen-xl tw-min-h-[500px] tw-m-auto tw-dark`}
      >
        {matchTree && (
          <AddTeamsBracket matchTree={matchTree} setMatchTree={setMatchTree} />
        )}
      </div>
      <div className="tw-flex tw-flex-col tw-gap-[46px] tw-max-w-screen-lg tw-m-auto tw-w-full">
        {/* <ActionButton className='tw-self-center' variant='blue' onClick={handleBack} paddingX={16} paddingY={12}>
					<ShuffleIcon />
					<span className='tw-font-500 tw-text-20 tw-uppercase tw-font-sans'>Scramble Team Order</span>
				</ActionButton> */}
        <div className="tw-flex tw-flex-col tw-gap-16">
          <ActionButton
            variant="blue"
            gap={16}
            disabled={createDisabled}
            onClick={handleSaveTemplate}
          >
            <SaveIcon />
            <span className="tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
              Save As Template
            </span>
          </ActionButton>
          <ActionButton
            variant="green"
            gap={16}
            disabled={createDisabled}
            onClick={() => setShowModal(true)}
          >
            <PlayIcon />
            <span className="tw-font-500 tw-text-20 tw-uppercase tw-font-sans">
              Create Tournament
            </span>
          </ActionButton>
          {showModal && (
            <TextFieldModal
              submitButtonText={'Create'}
              onSubmit={handleSubmit}
              header={'Create Tournament'}
              input={input}
              setInput={setInput}
              hasError={hasError}
              setHasError={setHasError}
              loading={loading}
              errorText={'Tournament name is required'}
              placeholderText={'Tournament name...'}
              setShow={setShowModal}
              show={showModal}
            />
          )}
        </div>
      </div>
    </div>
  )
}
