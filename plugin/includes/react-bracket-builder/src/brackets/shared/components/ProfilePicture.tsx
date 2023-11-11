import React from 'react'
import { ReactComponent as UserIcon } from '../assets/user.svg'

export const ProfilePicture = ({
  src,
  alt,
  color,
  backgroundColor,
  shadow,
}: {
  src: string
  alt: string
  color?: string
  backgroundColor?: string
  shadow?: boolean
}) => {
  let className = `tw-h-52 tw-w-52 tw-flex tw-rounded-full tw-border-solid tw-border-1 tw-border-${color} tw-justify-center tw-items-center tw-bg-${backgroundColor}`
  className = shadow
    ? className + ` tw-shadow-lg tw-shadow-${color}`
    : className
  const colorClass = `tw-text-${color}`

  if (src) {
    return (
      <img
        className={
          shadow
            ? `tw-h-52 tw-w-52 tw-rounded-full tw-shadow-lg tw-shadow-${color}`
            : 'tw-h-52 tw-w-52 tw-rounded-full'
        }
        src={src}
        alt={alt}
      />
    )
  }
  return (
    <div className={className}>
      <UserIcon className={colorClass} />
    </div>
  )
}
