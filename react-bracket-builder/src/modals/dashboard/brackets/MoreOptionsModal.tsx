import { useState } from 'react'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ReactComponent as EditIcon } from '../../../assets/icons/pencil.svg'
import { ReactComponent as PercentIcon } from '../../../assets/icons/percent.svg'
import { ReactComponent as ShareIcon } from '../../../assets/icons/share.svg'
import { ReactComponent as CopyIcon } from '../../../assets/icons/copy.svg'
import { ReactComponent as TrashIcon } from '../../../assets/icons/trash.svg'
import { ReactComponent as DollarIcon } from '../../../assets/icons/dollar_shield.svg'
import { ReactComponent as LockIcon } from '../../../assets/icons/lock.svg'
import { BracketData } from './BracketData'
import { TournamentModalVisibility } from './TournamentModalVisibility'
import { loadBracketData } from '../../loadBracketData'

const BracketOptionButton = (props: {
  IconComponent: React.FunctionComponent
  label: string
  onClick: () => void
}) => {
  return (
    <button
      className="tw-flex tw-gap-10 tw-items-center tw-bg-transparent tw-text-white tw-uppercase tw-font-sans tw-border-none tw-cursor-pointer tw-p-0 hover:tw-text-white/75"
      onClick={props.onClick}
    >
      <div className="tw-h-24 tw-w-24">
        <props.IconComponent />
      </div>
      <span className="tw-font-500 tw-text-16">{props.label}</span>
    </button>
  )
}

const BracketOptionLink = (props: {
  IconComponent: React.FunctionComponent
  label: string
  url: string
}) => {
  return (
    <a
      href={props.url}
      className="tw-flex tw-gap-10 tw-items-center tw-text-white tw-uppercase tw-font-sans tw-cursor-pointer hover:tw-text-white/75"
    >
      <props.IconComponent />
      <span className="tw-font-500 tw-text-16">{props.label}</span>
    </a>
  )
}

interface BracketOptionAvailability {
  mostPopularPicks: boolean
  shareBracket: boolean
  editBracket: boolean
  setFee: boolean
  duplicateBracket: boolean
  lockTournament: boolean
  deleteBracket: boolean
}

export const MoreOptionsModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  showModal: (modalName: keyof TournamentModalVisibility) => void
  bracketData: BracketData
  setBracketData: (data: BracketData) => void
}) => {
  const [bracketOptions, setBracketOptions] =
    useState<BracketOptionAvailability>({
      mostPopularPicks: false,
      shareBracket: false,
      editBracket: false,
      setFee: false,
      duplicateBracket: false,
      lockTournament: false,
      deleteBracket: false,
    })
  addClickHandlers({
    buttonClassName: 'wpbb-more-options-button',
    onButtonClick: (b) => {
      loadBracketData(b, props.setBracketData)
      setBracketOptions({
        mostPopularPicks: b.dataset.mostPopularPicks === 'true' ? true : false,
        shareBracket: b.dataset.shareBracket === 'true' ? true : false,
        editBracket: b.dataset.editBracket === 'true' ? true : false,
        setFee: b.dataset.setFee === 'true' ? true : false,
        duplicateBracket: b.dataset.duplicateBracket === 'true' ? true : false,
        lockTournament: b.dataset.lockTournament === 'true' ? true : false,
        deleteBracket: b.dataset.deleteBracket === 'true' ? true : false,
      })

      props.setShow(true)
    },
  })
  return (
    <Modal show={props.show} setShow={props.setShow} usePadding={false}>
      <div className="tw-flex tw-flex-col tw-gap-15 tw-p-20">
        {bracketOptions.mostPopularPicks && (
          <BracketOptionLink
            IconComponent={PercentIcon}
            label="Most Popular Picks"
            url={props.bracketData.mostPopularPicksUrl}
          />
        )}
        {bracketOptions.editBracket && (
          <BracketOptionButton
            IconComponent={EditIcon}
            label="Edit Info"
            onClick={() => {
              props.showModal('editBracket')
            }}
          />
        )}
        {bracketOptions.setFee && (
          <BracketOptionButton
            IconComponent={DollarIcon}
            label="Set Fee"
            onClick={() => {
              props.showModal('setTournamentFee')
            }}
          />
        )}
        {bracketOptions.shareBracket && (
          <BracketOptionButton
            IconComponent={ShareIcon}
            label="Share"
            onClick={() => {
              props.showModal('shareBracket')
            }}
          />
        )}
        {bracketOptions.duplicateBracket && (
          <BracketOptionLink
            IconComponent={CopyIcon}
            label="Duplicate"
            url={props.bracketData.copyBracketUrl}
          />
        )}
        {bracketOptions.lockTournament && (
          <BracketOptionButton
            IconComponent={LockIcon}
            label="Lock"
            onClick={() => {
              props.showModal('lockLiveTournament')
            }}
          />
        )}
        {bracketOptions.deleteBracket && (
          <BracketOptionButton
            IconComponent={TrashIcon}
            label="Delete"
            onClick={() => {
              props.showModal('deleteBracket')
            }}
          />
        )}
      </div>
    </Modal>
  )
}
